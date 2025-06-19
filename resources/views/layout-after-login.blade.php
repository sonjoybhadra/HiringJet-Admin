<?php
use App\Helpers\Helper;
?>
<!doctype html>
<html
  lang="en"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?=config('constants.admin_assets_url')?>assets/"
  data-template="vertical-menu-template-no-customizer"
  data-style="light">
  <head>
    <?=$head?>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <?=$sidebar?>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
            <?=$header?>
          </nav>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
              <?=$maincontent?>
            <!-- / Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <?=$footer?>
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>

      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/popper/popper.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/js/bootstrap.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/hammer/hammer.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/i18n/i18n.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/swiper/swiper.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

    <!-- Main JS -->
    <script src="<?=config('constants.admin_assets_url')?>assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="<?=config('constants.admin_assets_url')?>assets/js/dashboards-analytics.js"></script>
    <script type="text/javascript">
      $(function(){
        $('.autohide').delay(5000).fadeOut('slow');
      });
      let dotInterval;
      function startDotAnimation() {
        const dotElement = document.getElementById('dot-animation');
        let dotCount = 1;

        dotInterval = setInterval(() => {
            dotCount = (dotCount % 3) + 1;
            dotElement.textContent = '.'.repeat(dotCount);
        }, 500);
      }

      function stopDotAnimation() {
        clearInterval(dotInterval);
        document.getElementById('dot-animation').textContent = '.'; // reset
      }
    </script>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.1.0/ckeditor5.css" />
    <script type="importmap">
      {
          "imports": {
              "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/43.1.0/ckeditor5.js",
              "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/43.1.0/"
          }
      }
    </script>
    <script type="module">
      import {
          ClassicEditor,
          Essentials,
          Bold,
          Italic,
          Strikethrough,
          Subscript,
          Superscript,
          CodeBlock,
          Font,
          Link,
          List,
          Paragraph,
          Image,
          ImageCaption,
          ImageResize,
          ImageStyle,
          ImageToolbar,
          LinkImage,
          PictureEditing,
          ImageUpload,
          CloudServices,
          CKBox,
          CKBoxImageEdit,
          SourceEditing,
          ImageInsert
      } from 'ckeditor5';

      for (let i = 0; i <= 50; i++) {
        ClassicEditor
          .create( document.querySelector( '#ckeditor' + i ), {
            plugins: [ Essentials, Bold, Italic, Strikethrough, Subscript, Superscript, CodeBlock, Font, Link, List, Paragraph, Image, ImageToolbar, ImageCaption, ImageStyle, ImageResize, LinkImage, PictureEditing, ImageUpload, CloudServices, CKBox, CKBoxImageEdit, SourceEditing, ImageInsert ],
            toolbar: {
              items: [
                'undo', 'redo',
                '|',
                'heading',
                '|',
                'sourceEditing',
                '|',
                'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor', 'formatPainter',
                '|',
                'bold', 'italic', 'strikethrough', 'subscript', 'superscript', 'code',
                '|',
                'link', 'uploadImage', 'blockQuote', 'codeBlock',
                '|',
                'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent',
                '|',
                'ckbox', 'ckboxImageEdit', 'toggleImageCaption', 'imageTextAlternative', 'ckboxImageEdit',
                '|',
                'imageStyle:block',
                'imageStyle:side',
                '|',
                'toggleImageCaption',
                'imageTextAlternative',
                '|',
                'linkImage', 'insertImage', 'insertImageViaUrl'
              ]
            },
            menuBar: {
              isVisible: true
            }
          })
          .then( /* ... */ )
          .catch( /* ... */ );
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/js/lightbox.min.js"></script>
  </body>
</html>