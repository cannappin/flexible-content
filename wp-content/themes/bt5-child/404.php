<?php
/**
 * Template Name: Not found
 * Description: Page template 404 Not found.
 *
 */

get_header();



$search_enabled = get_theme_mod( 'search_enabled', '1' ); // Get custom meta-value.
?>


<section class="container p-5" style="height:400px;">
	<div class="row text-center justify-content-center">
		<h1>Page introuvable</h1>
		<p>Erreur 404</p>
		<a href="/" title="Accueil" class="mainbtn col-12 col-sm-5">Revenir à l'accueil</a>
	</div>
</section>


<?php
get_footer();