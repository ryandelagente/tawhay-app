<!-- Photo credits start -->
<div class="section photo-credits-page">
  <div class="container">
    <div class="section-title-wrap section-header text-center">
      <h5 class="custom-primary">Photo Credits</h5>
      <h2 class="title">Menu photography</h2>
      <p class="subtitle">
        Some menu photos are illustrative and come from Wikimedia Commons under the licences listed below.
        Dishes served at Tawhay may look different.
      </p>
    </div>
    <?php if ($credits): ?>
      <div class="table-responsive">
        <table class="table photo-credits-table">
          <thead>
            <tr>
              <th>Dish</th>
              <th>Photo</th>
              <th>Author</th>
              <th>Licence</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($credits as $credit): ?>
              <tr>
                <td><?php echo escape_output($credit->dish); ?></td>
                <td><a href="<?php echo escape_output($credit->source); ?>" target="_blank" rel="noopener"><?php echo escape_output($credit->title); ?></a></td>
                <td><?php echo escape_output($credit->author); ?></td>
                <td>
                  <?php if ($credit->license_url): ?>
                    <a href="<?php echo escape_output($credit->license_url); ?>" target="_blank" rel="noopener"><?php echo escape_output($credit->license); ?></a>
                  <?php else: ?>
                    <?php echo escape_output($credit->license); ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<!-- Photo credits end -->
