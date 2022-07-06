<?php

/*
    Template Name: General
*/

?>

<?php get_header();?>

<?php
if (have_rows('flex_content')) :
    while (have_rows('flex_content')) : the_row();
?>
        <!-- Bande de page -->
        <?php if( get_row_layout() == 'flex_title' ) :
            $title          =   get_sub_field('title');
            $disposition    =   get_sub_field('disposition_title');
            $description    =   get_sub_field('description');
            $image          =   get_sub_field('image');
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="bandeau" style="background-image:url('<?php echo (!empty(esc_url($image['url'])) ? esc_url($image['url']) : '/'); ?>'); background-repeat: no-repeat;background-size: cover;">
                    <h1 class="<?php echo $disposition; ?> mb-3 "><?php echo $title; ?></h1>
                    <?php echo $description; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>    
        <!-- Afficher projet -->
        <?php if( get_row_layout() == 'flex_project' ) : ?>
            <?php
                $args = array(
                    'post_type' => 'realisation',
                    'posts_per_page' => -1
                );
                $query = new WP_Query($args);
                if ($query->have_posts() ) : 
                    ?>
                    <div class="container-fluid">
                    <div class="row justify-content-center">
                    <?php
                    while ( $query->have_posts() ) : $query->the_post();
                            ?>
                                    <a href="<?php echo get_permalink(get_the_ID()) ?>" class="wrapper col-3 p-0" style="position: relative; background-image:url('<?php echo get_the_post_thumbnail_url() ?>');background-size: cover;">
                                        <div class="filter_project"></div>
                                        <div style="position: relative; top: 200px; left: 15px; z-index: 2;"><p class="link_all_project"><?php echo get_the_title() ?></p></div>
                                    </a>        
                            <?php
                    endwhile;
                    ?>
                    </div>
                    </div>
                    <?php 
                    wp_reset_postdata();
                endif;
            ?>
        <?php endif; ?>    
        <!-- text - image -->
        <?php if( get_row_layout() == 'flex_texte_image' ) :
            $section        = get_sub_field('section_text_image');
            $disposition    = get_sub_field('disposition_text_image');
            $content        = get_sub_field('content_text_image');
            $image          = get_sub_field('image_text_image');
        ?>
            <div class="<?php echo $section ?> my-5">
                <div class="<?php echo $disposition ?> row">
                    <div class="col-lg-6 d-flex align-items-center">
                        <!-- S'affiche 3 fois, à debuger -->
                        <div style="<?php echo $disposition == "flex-row-column" ? "text-align: start;" : "text-align: end;" ?>"><?php echo $content ?></div>
                    </div>
                    <div class="text_img col-lg-6 d-flex <?php echo $disposition == "flex-row-column" ? "justify-content-end" : "justify-content-start" ?>">
                        <img src="<?php echo (!empty(esc_url($image['url'])) ? esc_url($image['url']) : '/');?>" alt="<?php echo $image['alt']; ?>" class="w-100">
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- Titre, texte, button - image -->
        <?php if( get_row_layout() == 'flex_texte_button_image' ) :
            $section        = get_sub_field('section_text_image');
            $disposition    = get_sub_field('disposition_text_image');
            $content_title  = get_sub_field('content_title');
            $content        = get_sub_field('content_text_image');
            $image          = get_sub_field('image_text_image');
            $button_title   = get_sub_field('button_title');
            $button_link    = get_sub_field('button_link');
        ?>
            <div class="<?php echo $section ?> my-5">
                <div class="<?php echo $disposition ?> row">
                    <div class="col-lg-6 d-flex <?php echo $disposition == "flex-row-column" ? "justify-content-start" : "justify-content-end" ?> img_text_btn_flex">
                        <img src="<?php echo (!empty(esc_url($image['url'])) ? esc_url($image['url']) : '/');?>" alt="<?php echo $image['alt']; ?>" class="w-75">
                    </div>
                    <div class="col-lg-6 d-flex justify-content-center flex-column">
                        <!-- S'affiche 3 fois, à debuger -->
                        <h2><?php echo $content_title ?></h2>
                        <p style="<?php echo $disposition == "flex-row-column" ? "text-align: start;" : "text-align: end;" ?>"><?php echo $content ?></p>
                        <a class="mainbtn" style="width: fit-content;" href="<?php echo $button_link?>"><?php echo $button_title ?></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- Afficher le formulaire de contact -->
        <?php if( get_row_layout() == 'flex_contact' ) :
            $adresse        =   get_field("adresse", "option");
            $phone          =   get_field("phone", "option");
            $mail           =   get_field("mail", "option");
        ?>
            <div class="container-fluid pad_general" id="contact">
                <div class="row">
                    <div class="col-lg-6 col-sm-12 col-md-12">
                        <h2 class="contact_margin text_gras">CONTACT</h2>
                        <p><?php echo do_shortcode('[contact-form-7 id="49" title="Contact"]'); ?></p>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-md-6">
                        <h2 class="contact_margin text_gras">NOS COORDONÉES</h2>
                        <div class="d-flex">
                            <i class="geo_icon fas fa-map-marker-alt"></i>
                                <p><?php echo $adresse; ?></p>
                    </div>
                        <div class="d-flex">
                            <i class="fas fa-at"></i>
                                <p><?php echo $mail; ?></p>
                        </div>
                        <div class="d-flex">
                            <i class="fas fa-phone-alt"></i>
                                <p><?php echo $phone; ?></p>
                        </div>
            </div>
                    <div class="col-12 d-flex">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2886.8370606847825!2d1.4473028159518266!3d43.65155826055403!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12aea36697bc31f7%3A0x13a6b6d005ce0705!2sBatinergy!5e0!3m2!1sfr!2sfr!4v1639583008863!5m2!1sfr!2sfr" width="100%" height="300" style="border-radius: 50px; margin-bottom: 70px; margin-top: 55px;" allowfullscreen="false" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- Afficher un bouton -->
        <?php if( get_row_layout() == 'flex_button' ) :
            $section         = get_sub_field('section_button');
            $title_button    = get_sub_field('title_button');
            $link_button     = get_sub_field('link_button');
            $display_arrow     = get_sub_field('display_arrow');
        ?>
            <div class="<?php echo $section ?> mt-5" id="flex_button">
                <div class="row">
                    <a class="mainbtn" style="width: fit-content;" href="<?php echo $link_button ?>"><?php echo $title_button ?></a>
                </div>
            </div>
        <?php endif; ?>
        <!-- Afficher un bouton -->
        <?php if( get_row_layout() == 'title_text' ) :
            $section         = get_sub_field('section');
            $title    = get_sub_field('title');
            $content    = get_sub_field('content');
        ?>
            <div class="<?php echo $section; ?> mt-5">
                <div class="row">
                    <h2><?php echo $title; ?></h2>
                    <p><?php echo $content; ?></p>
                </div>
            </div>
        <?php endif; ?>
    <?php endwhile; ?>
<?php endif; ?>

<?php get_footer(); ?>