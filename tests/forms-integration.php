<?php
/**
 * Local WordPress integration checks; run only against a disposable or backed-up database.
 * Usage: php tests/forms-integration.php
 * Configure the CLI's MySQL connection settings before running this file.
 * All email delivery is intercepted and all newly created fixtures are removed.
 */
if ( PHP_SAPI !== 'cli' ) { http_response_code(404); exit; }
$wp_root = dirname(__DIR__);
while ( ! is_file($wp_root . '/wp-load.php') ) {
    $parent = dirname($wp_root);
    if ( $parent === $wp_root ) { fwrite(STDERR, "Cannot find WordPress wp-load.php.\n"); exit(1); }
    $wp_root = $parent;
}
define('DISABLE_WP_CRON', true);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '198.51.100.226';
$_SERVER['REQUEST_URI'] = '/';
$mail_calls = 0; $mail_result = true;
require_once $wp_root . '/wp-includes/plugin.php';
add_filter('pre_wp_mail', function($pre,$atts) use (&$mail_calls,&$mail_result) { $mail_calls++; return $mail_result; }, PHP_INT_MAX, 2);
require $wp_root . '/wp-load.php';
$_SERVER['HTTP_HOST'] = wp_parse_url(home_url('/'), PHP_URL_HOST);
class FW_Test_Redirect extends Exception { public $url; public $status; function __construct($url, $status) { $this->url=$url; $this->status=$status; } }
$fixture_posts=[]; $fixture_terms=[]; $fixture_tokens=[]; $checks=[]; $mail_calls=0; $mail_result=true;
add_filter('wp_redirect', function($url,$status) { throw new FW_Test_Redirect($url,$status); }, 10, 2);

add_action('wp_insert_post', function($id,$post) use (&$fixture_posts) { if (strpos($post->post_title,'FWTEST')===0) { $fixture_posts[]=$id; } },10,2);
add_action('created_term', function($id,$tt,$taxonomy) use (&$fixture_terms) { if ($taxonomy==='mechanic_city') { $term=get_term($id,$taxonomy); if(strpos($term->name,'Codex Testort')===0) { $fixture_terms[]=$id; } } },10,3);
function fw_assert($condition,$label) { global $checks; if(!$condition) { throw new RuntimeException('FAIL: '.$label); } $checks[]=$label; }
function fw_issue($kind) { global $fixture_tokens; $token=findewerkstatt_form_submission_token($kind); $fixture_tokens[]=$token; return $token; }
function fw_submit($kind,$values) { $_POST=wp_slash($values); try { ($kind==='contact' ? 'findewerkstatt_contact_submit' : 'findewerkstatt_registration_submit')(); } catch(FW_Test_Redirect $redirect) { parse_str(parse_url($redirect->url,PHP_URL_QUERY),$q); $notice=get_transient('fw_form_notice_'.$q['fw_notice']); delete_transient('fw_form_notice_'.$q['fw_notice']); fw_assert($redirect->status===303,'303 redirect: '.$kind); fw_assert(array_keys($q)===array('fw_notice') && preg_match('/^[a-zA-Z0-9]{32}$/',$q['fw_notice']) && strtok($redirect->url,'?')===findewerkstatt_page_url($kind==='contact'?'kontakt':'werkstatt-anmelden'),'Redirect returns to the form with an opaque token only: '.$kind); return $notice; } throw new RuntimeException('Handler did not redirect'); }
function fw_cleanup() { global $fixture_posts,$fixture_terms,$fixture_tokens; foreach(array_unique($fixture_posts) as $id) { $post=get_post($id); if($post && strpos($post->post_title,'FWTEST')===0) { wp_delete_post($id,true); } } foreach(array_unique($fixture_terms) as $id) { $term=get_term($id,'mechanic_city'); if($term && strpos($term->name,'Codex Testort')===0) { wp_delete_term($id,'mechanic_city'); } } foreach($fixture_tokens as $token) { $hash=hash('sha256',$token); $key='fw_form_lock_'.$hash; delete_transient('fw_form_token_'.$hash); delete_option($key); wp_clear_scheduled_hook('fw_form_cleanup_lock',array($key)); } foreach(array('contact','registration') as $kind) { delete_transient('fw_rate_'.hash_hmac('sha256',$kind.'|198.51.100.226',wp_salt('nonce'))); } }
register_shutdown_function('fw_cleanup');
try {
    $base=['name'=>"FWTEST O'Connor",'email'=>'fwtest@example.invalid','subject'=>'FWTEST Kontakt','message'=>"Bitte prüfen Sie C:\\Werkstatt\\. Vielen Dank.",'privacy'=>'1','fw_kontakt_nonce'=>wp_create_nonce('fw_send_kontakt'),'fw_submission_token'=>fw_issue('contact')];
    $bad=$base; $bad['email']='invalid'; $notice=fw_submit('contact',$bad);
    fw_assert($notice['type']==='error' && $notice['values']['name']==="FWTEST O'Connor",'Contact invalid email and sticky unslashed name');
    fw_assert(count($fixture_posts)===0 && $mail_calls===0,'Invalid contact never saved or mailed');
    $expired=$base; $expired['fw_kontakt_nonce']='invalid'; $notice=fw_submit('contact',$expired);
    fw_assert($notice['type']==='error' && $notice['values']['message']===$base['message'] && count($fixture_posts)===0 && $mail_calls===0,'Expired nonce preserves contact values without saving or mailing');
    $notice=fw_submit('contact',$base); $contact_id=end($fixture_posts);
    fw_assert($notice['type']==='success' && $notice['received'],'Contact successful receipt');
    fw_assert(get_post_status($contact_id)==='private' && strpos(get_post_field('post_content',$contact_id),"O'Connor")!==false && strpos(get_post_field('post_content',$contact_id),'C:\\Werkstatt\\')!==false,'Private contact preserves apostrophe and backslashes');
    fw_assert(get_post_meta($contact_id,'_fw_notification_sent',true)==='yes' && $mail_calls===1,'Mail success recorded, no external email sent');
    fw_assert(!current_user_can('read_post',$contact_id),'Visitor cannot read private contact');
    $administrator_ids = get_users(array('role' => 'administrator', 'fields' => 'ID', 'number' => 1));
    fw_assert(!empty($administrator_ids), 'An administrator is available for the private inbox check');
    wp_set_current_user((int) $administrator_ids[0]); fw_assert(current_user_can('read_post',$contact_id),'Administrator can read private contact'); wp_set_current_user(0);
    $before=count($fixture_posts); $notice=fw_submit('contact',$base);
    fw_assert($notice['received'] && count($fixture_posts)===$before && $mail_calls===1,'Repeated contact token cannot save or email twice');
    $mail_result=false; $failed=$base; $failed['subject']='FWTEST Mail failure'; $failed['fw_submission_token']=fw_issue('contact'); $notice=fw_submit('contact',$failed); $failed_id=end($fixture_posts);
    fw_assert($notice['type']==='warning' && $notice['received'] && get_post_meta($failed_id,'_fw_notification_sent',true)==='no' && get_post_status($failed_id)==='private','Failed notification retains private message with truthful warning');
    $hp=$base; $hp['company_website']='bot'; $hp['fw_submission_token']=fw_issue('contact'); $before=count($fixture_posts); $notice=fw_submit('contact',$hp);
    fw_assert($notice['type']==='error' && count($fixture_posts)===$before,'Contact honeypot blocks saving');
    $service_map=findewerkstatt_form_terms('service_type'); $brand_map=findewerkstatt_form_terms('car_brand');
    fw_assert(count($service_map)>0 && count($brand_map)>0,'Known service and brand options available');
    // Contact checks remain anonymous; workshop registration now requires an account.
    wp_set_current_user((int) $administrator_ids[0]);
    fw_assert(current_user_can('manage_options'),'Registration fixtures use the existing administrator without changing the account');
    $reg=['company_name'=>"FWTEST Meisterbetrieb O'Connor",'email'=>'fwtest@example.invalid','phone'=>'030 1234567','whatsapp'=>'0170 1234567','website'=>'www.fwtest.example.invalid/leistungen','city'=>'München','bundesland'=>'bayern','plz'=>'80331','privacy'=>'1','services'=>[array_key_first($service_map)],'brands'=>[array_key_first($brand_map)],'description'=>"Werkstatt O'Connor",'fw_register_nonce'=>wp_create_nonce('fw_register_workshop'),'fw_submission_token'=>fw_issue('registration')];
    $wrong=$reg; $wrong['bundesland']='hessen'; $before=count($fixture_posts); $notice=fw_submit('registration',$wrong);
    fw_assert($notice['type']==='error' && count($fixture_posts)===$before,'Known city rejects wrong state');
    $spam=$reg; $spam['services']=['fw-test-arbitrary-term']; $notice=fw_submit('registration',$spam);
    fw_assert($notice['type']==='error' && !get_term_by('slug','fw-test-arbitrary-term','service_type'),'Arbitrary service cannot create taxonomy terms');
    $array=$reg; $array['fw_register_nonce']=[]; $array['company_name']=[]; $notice=fw_submit('registration',$array);
    fw_assert($notice['type']==='error','Array inputs and array nonce handled safely');
    foreach(array('mailto:info@example.invalid','https://user:password@example.invalid','https://example..invalid') as $invalid_website) {
        $invalid=$reg; $invalid['website']=$invalid_website; $before=count($fixture_posts); $notice=fw_submit('registration',$invalid);
        fw_assert($notice['type']==='error' && count($fixture_posts)===$before,'Invalid business website cannot be stored: '.$invalid_website);
    }
    $notice=fw_submit('registration',$reg); $registration_id=end($fixture_posts); $city=wp_get_post_terms($registration_id,'mechanic_city');
    fw_assert($notice['received'] && get_post_status($registration_id)==='pending','Valid registration remains pending');
    fw_assert(count($city)===1 && $city[0]->term_id===findewerkstatt_registration_location('Muenchen','bayern')['term_id'] && in_array(get_term_by('slug','bayern','mechanic_city')->term_id,get_ancestors($city[0]->term_id,'mechanic_city')),'München reuses existing city under Bayern');
    fw_assert(get_post_meta($registration_id,'_mechanic_phone',true)==='+49301234567' && get_post_meta($registration_id,'_mechanic_whatsapp',true)==='+491701234567','National phone and explicit WhatsApp normalized internationally');
    fw_assert(get_post_meta($registration_id,'_mechanic_website',true)==='https://www.fwtest.example.invalid/leistungen','Domain-only website becomes a usable HTTPS link');
    fw_assert(get_post_meta($registration_id,'_mechanic_email_public',true)==='no' && get_post_meta($registration_id,'_mechanic_is_verified',true)==='no','Applicant email private and verification not claimed');
    fw_assert(count(wp_get_post_terms($registration_id,'service_type'))===1 && count(wp_get_post_terms($registration_id,'car_brand'))===1,'Existing service and brand terms assigned');
    $before=count($fixture_posts); $notice=fw_submit('registration',$reg); fw_assert($notice['received'] && count($fixture_posts)===$before,'Repeated registration token cannot create duplicates');
    $dup=$reg; $dup['fw_submission_token']=fw_issue('registration'); $notice=fw_submit('registration',$dup); fw_assert($notice['received'] && count($fixture_posts)===$before,'Same pending business not duplicated by a fresh form');
    $alias=$reg; $alias['city']='Munich'; $alias['company_name']=strtoupper($reg['company_name']); $alias['fw_submission_token']=fw_issue('registration'); $notice=fw_submit('registration',$alias);
    fw_assert($notice['received'] && count($fixture_posts)===$before,'Accepted municipality alias and company capitalization reuse the same pending business');
    $canonical_fingerprint=get_post_meta($registration_id,'_fw_registration_fingerprint',true);
    $legacy_fingerprint=hash('sha256',strtolower($reg['company_name'].'|'.$reg['email']).'|'.findewerkstatt_german_location_slug($reg['city']).'|'.$reg['bundesland']);
    update_post_meta($registration_id,'_fw_registration_fingerprint',$legacy_fingerprint);
    $legacy=$reg; $legacy['fw_submission_token']=fw_issue('registration'); $notice=fw_submit('registration',$legacy);
    fw_assert($notice['received'] && count($fixture_posts)===$before,'Existing pending registrations with the previous fingerprint remain deduplicated');
    update_post_meta($registration_id,'_fw_registration_fingerprint',$canonical_fingerprint);
    $new=$reg; $new['company_name']='FWTEST Neuer Betrieb'; $new['city']='Codex Testort'; $new['public_email']='1'; $new['fw_submission_token']=fw_issue('registration'); $notice=fw_submit('registration',$new); $new_id=end($fixture_posts); $new_city=wp_get_post_terms($new_id,'mechanic_city');
    fw_assert($notice['received'] && $new_city[0]->parent===get_term_by('slug','bayern','mechanic_city')->term_id,'New municipality has selected state parent');
    fw_assert(get_post_meta($new_id,'_mechanic_email_public',true)==='yes','Explicit public email consent preserved');
    $before=count($fixture_posts); $new_duplicate=$new; $new_duplicate['fw_submission_token']=fw_issue('registration'); $notice=fw_submit('registration',$new_duplicate);
    fw_assert($notice['received'] && count($fixture_posts)===$before,'Newly created municipality has a stable duplicate identity on a fresh form');
    $third=$reg; $third['company_name']='FWTEST Dritter Betrieb'; $third['fw_submission_token']=fw_issue('registration'); $notice=fw_submit('registration',$third);
    fw_assert($notice['received'] && count($fixture_posts)===$before+1,'Repeated duplicate submissions do not consume the three-business hourly quota');
    $fourth=$reg; $fourth['company_name']='FWTEST Vierter Betrieb'; $fourth['fw_submission_token']=fw_issue('registration'); $before=count($fixture_posts); $notice=fw_submit('registration',$fourth);
    fw_assert($notice['type']==='error' && !$notice['received'] && count($fixture_posts)===$before,'Fourth distinct registration is throttled before creating a pending profile');
    $after_quota=$reg; $after_quota['fw_submission_token']=fw_issue('registration'); $notice=fw_submit('registration',$after_quota);
    fw_assert($notice['received'] && count($fixture_posts)===$before,'Already received business remains acknowledged after the hourly quota');
    $notice_token=wp_generate_password(32,false,false); $notice_value=array('kind'=>'contact','type'=>'error','values'=>array('name'=>'FWTEST Private notice'));
    set_transient('fw_form_notice_'.$notice_token,$notice_value,MINUTE_IN_SECONDS); $_GET=array('fw_notice'=>$notice_token);
    fw_assert(findewerkstatt_get_form_notice('registration')===array(),'Notice tokens cannot be read through the wrong form');
    fw_assert(findewerkstatt_get_form_notice('contact')===$notice_value && findewerkstatt_get_form_notice('contact')===array(),'Private form notice is delivered once and removed on refresh');
    $_GET=array('fw_notice'=>array($notice_token)); fw_assert(findewerkstatt_get_form_notice('contact')===array(),'Array-shaped notice token is ignored safely'); $_GET=array();
    $old=wp_insert_post(['post_type'=>'fw_contact_message','post_status'=>'private','post_title'=>'FWTEST Retention','post_content'=>'Old enquiry','post_date'=>gmdate('Y-m-d H:i:s',time()-181*DAY_IN_SECONDS),'post_date_gmt'=>gmdate('Y-m-d H:i:s',time()-181*DAY_IN_SECONDS)]);
    // Restrict the cleanup query to this fixture; never trash real historical enquiries in a test.
    $retention_scope = function($query) use ($old) { if ($query->get('post_type') === 'fw_contact_message' && $query->get('date_query')) { $query->set('post__in', array($old)); } };
    add_action('pre_get_posts', $retention_scope);
    try { findewerkstatt_contact_retention(); } finally { remove_action('pre_get_posts', $retention_scope); }
    fw_assert(get_post_status($old)==='trash','180-day cleanup moves enquiry to recoverable trash');
    fw_cleanup();
    foreach(array_unique($fixture_posts) as $id) { fw_assert(!get_post($id),'Fixture post removed: '.$id); }
    foreach(array_unique($fixture_terms) as $id) { fw_assert(!get_term($id,'mechanic_city'),'Fixture municipality removed: '.$id); }
    $token_cleanup=true;
    foreach($fixture_tokens as $token) { $hash=hash('sha256',$token); $key='fw_form_lock_'.$hash; if(get_transient('fw_form_token_'.$hash)!==false || get_option($key)!==false || wp_next_scheduled('fw_form_cleanup_lock',array($key))!==false) { $token_cleanup=false; } }
    fw_assert($token_cleanup,'Submission tokens, locks and scheduled cleanup fixtures removed');
    echo json_encode(['passed'=>count($checks),'checks'=>$checks,'fixture_cleanup'=>'complete','external_mail_sent'=>0],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
} catch(Throwable $e) { fw_cleanup(); echo json_encode(['error'=>$e->getMessage(),'checks'=>$checks],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); exit(1); }
