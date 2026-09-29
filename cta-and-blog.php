<!-- CTA (Kayıt Ol) Banner Alanı -->
<div class="cta-banner-section" style="max-width: 1200px; margin: 60px auto; padding: 0 15px;">
    <div style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.3) 100%), url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; border-radius: 8px; padding: 80px 50px; color: white;">
        <div style="max-width: 500px;">
            <h2 style="font-size: 36px; color: white; margin: 0 0 15px 0;">Ücretsiz Olarak!</h2>
            <p style="font-size: 18px; line-height: 1.6; margin: 0 0 30px 0; color: #e5e7eb;">Web sitemize kayıt olma fırsatını kaçırmayın! Son Tarih: 28 Eylül 2025</p>
            <a href="<?php echo esc_url( home_url( '/usta-ekle' ) ); ?>" style="display: inline-block; background-color: #f91942; color: white; padding: 15px 35px; border-radius: 30px; font-weight: bold; text-decoration: none; font-size: 16px; transition: 0.3s; box-shadow: 0 4px 15px rgba(249,25,66,0.3);">Firmanızı Ekleyin!</a>
        </div>
    </div>
</div>

<!-- Yararlı Makaleler (Blog) Alanı -->
<div class="blog-section" style="max-width: 1200px; margin: 80px auto; padding: 0 15px; text-align: center;">
    <h2 style="font-size: 32px; color: #333; margin-bottom: 50px;">Yararlı Makaleler</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; text-align: left;">
        <?php
        // Son 3 blog yazısını çek (Post Type: post)
        $blog_args = array(
            'post_type' => 'post',
            'posts_per_page' => 3
        );
        $blog_query = new WP_Query( $blog_args );

        if ( $blog_query->have_posts() ) :
            while ( $blog_query->have_posts() ) : $blog_query->the_post();
                ?>
                <?php 
                    $c_img = function_exists('ototamir_get_smart_post_image') 
                        ? ototamir_get_smart_post_image(get_the_ID(), 500, 300) 
                        : (get_the_post_thumbnail_url(get_the_ID(), 'medium_large') ?: 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=500&q=75');
                    if ( empty($c_img) || strpos($c_img, 'photo-1486006920555-c77dce18193b') !== false ) {
                        $c_img = get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp';
                    }
                ?>
                <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s;">
                    <a href="<?php the_permalink(); ?>" style="display: block; height: 200px; background-color: #e5e7eb; background-size: cover; background-position: center; background-image: url('<?php echo esc_url($c_img); ?>');">
                        <!-- Resim Alanı -->
                    </a>
                    <div style="padding: 25px;">
                        <h4 style="margin: 0 0 15px 0; font-size: 20px; line-height: 1.4;">
                            <a href="<?php the_permalink(); ?>" style="color: #333; text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='#f97316'" onmouseout="this.style.color='#333'"><?php the_title(); ?></a>
                        </h4>
                        <div style="color: #666; font-size: 15px; line-height: 1.6; margin-bottom: 20px;">
                            <?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
                        </div>
                        <a href="<?php the_permalink(); ?>" style="color: #f97316; font-weight: 600; text-decoration: none;">Devamını Oku &rarr;</a>
                    </div>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p style="text-align: center; width: 100%; color: #666;">Henüz makale eklenmemiş.</p>';
        endif;
        ?>
    </div>
</div>
