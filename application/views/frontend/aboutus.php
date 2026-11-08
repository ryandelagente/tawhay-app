<?php
$company_info = getMainCompany(); 
$about_us = isset($company_info->about_us) && $company_info->about_us?json_decode($company_info->about_us):'';
$common_menu_page = isset($company_info->common_menu_page) && $company_info->common_menu_page?json_decode($company_info->common_menu_page):'';
?>

  <!-- About us start -->
  <div class="section about-us-page">
    <div class="container">
      <div class="row align-items-center">

        <div class="col-lg-6 mb-lg-30 ct-single-img-wrapper">
          <img src="<?php echo base_url();?>uploads/about_us/<?php echo isset($about_us->about_us_image) && $about_us->about_us_image ? $about_us->about_us_image : '' ?>" alt="">
        </div>
        <div class="col-lg-6">
          <div class="section-title-wrap mr-lg-30">
            <h5 class="custom-primary"><?php echo isset($about_us->about_us_title) && $about_us->about_us_title ? $about_us->about_us_title : '' ?></h5>
            <h2 class="title"><?php echo isset($about_us->abous_us_heading) && $about_us->abous_us_heading ? $about_us->abous_us_heading : '' ?></h2>
            <p class="subtitle">
              <?php echo isset($about_us->about_us_des) && $about_us->about_us_des ? $about_us->about_us_des : '' ?>
            </p>
            <a href="<?php echo base_url()?>online-order" class="btn-custom"><?php echo lang('check_our_menu'); ?> <i class="fa fa-caret-right"></i></a>
          </div>
        </div>

      </div>
    </div>
  </div>
  <!-- About us End -->

  <!-- About us photos start -->
  <?php
  $about_photos = [
      ['tawhay-by-crabs-and-ribs-logo-green-wall-signage', 'Tawhay by Crabs and Ribs logo on the green wall'],
      ['tawhay-glass-roof-dining-area-buffet-setup', 'Glass-roof dining area with buffet setup at Tawhay'],
      ['tawhay-restaurant-interior-buffet-line-dining-hall', 'Tawhay restaurant dining hall and buffet line'],
      ['tawhay-restaurant-christmas-tree-wooden-staircase', 'Christmas tree beside the wooden staircase at Tawhay'],
      ['tawhay-restaurant-dining-tables-christmas-decor-view', 'Dining tables with Christmas decor at Tawhay'],
      ['tawhay-curry-mud-crab-with-spring-onion', 'Curry mud crab topped with spring onion'],
      ['tawhay-chili-garlic-crab-signature-dish', 'Chili garlic crab, a Tawhay signature dish'],
      ['tawhay-crab-spaghetti-in-creamy-yellow-sauce', 'Crab spaghetti in creamy yellow sauce'],
      ['tawhay-garlic-spaghetti-with-lime-and-fresh-tomato-salsa', 'Garlic spaghetti with lime and fresh tomato salsa'],
      ['tawhay-garlic-fried-rice-with-beef-tapa-and-scrambled-egg', 'Garlic fried rice with beef tapa and scrambled egg'],
      ['tawhay-egg-burger-with-potato-wedges', 'Egg burger with potato wedges'],
      ['tawhay-crabs-and-ribs-curry-crab-served-by-staff', 'Chef presenting curry crab at Tawhay'],
      ['tawhay-bar-liquor-and-spirits-shelf', 'Bar shelf of liquor and spirits at Tawhay'],
      ['tawhay-crabs-and-ribs-restaurant-owners-and-chef-team-photo', 'Tawhay owners and chefs in front of the green wall'],
      ['tawhay-crabs-and-ribs-kitchen-and-service-crew-group-photo', 'Tawhay kitchen and service crew group photo'],
      ['tawhay-service-staff-team-photo-green-wall', 'Tawhay service staff team photo'],
      ['tawhay-waiters-and-waitresses-with-christmas-tree', 'Tawhay waiters and waitresses beside the Christmas tree'],
      ['tawhay-open-kitchen-chefs-team-photo', 'Tawhay chefs in the open kitchen'],
  ];
  ?>
  <div class="section about-us-photos">
    <div class="container">
      <div class="section-title-wrap mb-4">
        <h2 class="title">Our Restaurant &amp; Food</h2>
      </div>
      <div class="row g-3">
        <?php foreach ($about_photos as $ph): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <img loading="lazy" decoding="async" class="w-100" style="aspect-ratio:1/1;object-fit:cover;border-radius:6px"
              src="<?php echo base_url(); ?>uploads/photo_gallery/<?php echo $ph[0]; ?>.jpg"
              alt="<?php echo htmlspecialchars($ph[1], ENT_QUOTES); ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <!-- About us photos End -->
