<?php
/**
 * Template voor detail pagina van een woning
 *
 * @package WordPress
 * @subpackage yesco-og plugin
 */
get_header();
?>
<div id="container">
  <div id="content">
    <?php
    // Retrieve post
    if (have_posts())
    {
	    while (have_posts())
      {
        the_post();
        
        $prices       = yog_retrievePrices();
        $specs        = yog_retrieveSpecs(array('Plaats', 'Type', 'SoortWoning', 'TypeWoning', 'Kenmerk', 'Bouwjaar', 'Aantalkamers', 'Oppervlakte', 'OppervlaktePerceel', 'Ligging', 'GarageType', 'TuinType', 'BergingType', 'PraktijkruimteType', 'EnergielabelKlasse', 'Status'));
        
        ?>
        <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
          <h1 class="entry-title"><?php the_title(); ?>, <?php echo yog_retrieveSpec('Plaats');?></h1>
          <h2 class="entry-price"><?php echo implode('<br />', $prices);?></h2>
          <div class="entry-content">
            <?php echo yog_retrievePhotoSlider();?>
            <h3>Kenmerken</h3>
            <?php
            if (yog_hasOpenHouse())
              echo '<p class="entry-specs">' . yog_getOpenHouse() . '</p>';
              
            foreach ($specs as $label => $value)
            {
              echo '<p class="entry-specs"><span class="label">' . $label . ':</span> ' . $value . '</p>';
            }
            ?>
            <h3>Omschrijving</h3>
            <?php the_content();?>
            <?php if (yog_hasLocation()) { ?>
              <h3>Locatie</h3>
              <div id="yesco-og-static-map-holder">
                <?php echo yog_retrieveStaticMap(); ?>
              </div>
              <?php echo yog_retrieveDynamicMap(); ?>
            <?php } ?>
          </div>
        </div>
        <?php
      }
    }
    ?>
  </div>
</div>
<?php get_footer();?>

