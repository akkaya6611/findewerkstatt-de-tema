<?php
/** Package selection when submitting a workshop or requesting more capacity. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$plans = $args['plans'] ?? findewerkstatt_membership_plans();
$selected_plan = $args['selected_plan'] ?? 'free';
$active_plan = $args['active_plan'] ?? 'free';
$owned_count = (int) ( $args['owned_count'] ?? 0 );
$request_only = ! empty( $args['request_only'] );
?>
<fieldset id="paket-auswahl" class="fw-form-section fw-registration-packages" aria-describedby="fw-registration-package-help">
    <legend><?php echo esc_html( findewerkstatt_t( '1. Paket auswählen *' ) ); ?></legend>
    <p class="fw-registration-package-intro"><?php echo esc_html( findewerkstatt_t( 'Wählen Sie das passende Paket für Ihren Betrieb.' ) ); ?></p>
    <div class="fw-registration-package-grid">
        <?php foreach ( $plans as $plan_id => $plan ) :
            $limit = (int) $plan['listing_limit'];
            $disabled = $request_only && $limit <= $owned_count;
            ?>
            <label class="fw-registration-package<?php echo $disabled ? ' fw-registration-package-disabled' : ''; ?>">
                <input type="radio" name="selected_plan" value="<?php echo esc_attr( $plan_id ); ?>" required <?php checked( $selected_plan, $plan_id ); ?> <?php disabled( $disabled ); ?>>
                <span class="fw-registration-package-card">
                    <strong class="fw-registration-package-name"><?php echo esc_html( $plan['name'] ); ?></strong>
                    <span class="fw-registration-package-price"><?php echo 'free' === $plan_id ? '0 €' : findewerkstatt_t( 'Preis folgt' ); ?></span>
                    <span class="fw-registration-package-limit"><?php echo esc_html( sprintf( _n( findewerkstatt_t( 'Bis zu %d Betriebseintrag' ), findewerkstatt_t( 'Bis zu %d Betriebseinträge' ), $limit, 'findewerkstatt' ), $limit ) ); ?></span>
                    <span class="fw-registration-package-status"><?php echo $active_plan === $plan_id ? findewerkstatt_t( 'Ihr aktuelles Paket' ) : ( 'free' === $plan_id ? findewerkstatt_t( 'Kostenloses Basispaket' ) : findewerkstatt_t( 'Unverbindlich anfragen' ) ); ?></span>
                    <?php if ( $disabled ) : ?><span class="fw-registration-package-unavailable"><?php echo esc_html( findewerkstatt_t( 'Kontingent bereits ausgeschöpft' ) ); ?></span><?php endif; ?>
                </span>
            </label>
        <?php endforeach; ?>
    </div>
    <p id="fw-registration-package-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Plus und Professional sind auf Anfrage erhältlich. Ein anderes Paket wird erst nach unserer manuellen Freigabe aktiv. Bis dahin gilt Ihr aktuelles Kontingent. Die Auswahl löst keine Zahlung oder kostenpflichtige Bestellung aus.' ) ); ?><?php if ( ! $request_only ) : ?> <?php echo esc_html( findewerkstatt_t( 'Wenn Sie ein anderes Paket wählen, wird die Paketanfrage zusammen mit dem neuen Eintrag übermittelt.' ) ); ?><?php endif; ?></p>
</fieldset>
