<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                <div class="mb-2 mb-md-0">
                  ©<?= Date('Y')?>, by Everton Rafael da Silva</a>
                </div>
                
              </div>
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
    </div>
    <!-- / Layout wrapper -->

    

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
<!--     <script src="<?= $_ENV['BASE_VENDOR']; ?>libs/jquery/jquery.js"></script>
 -->
     
    <script src="<?= $_ENV['BASE_VENDOR']; ?>libs/popper/popper.js"></script>
    <script src="<?= $_ENV['BASE_VENDOR']; ?>js/bootstrap.js"></script>
    <script src="<?= $_ENV['BASE_VENDOR']; ?>libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="<?= $_ENV['BASE_VENDOR']; ?>js/menu.js"></script>
    <!-- endbuild -->

    <!-- Decimal inputs accept only a point as separator (input[data-decimal]) -->
    <script src="<?= $_ENV['BASE_JS']; ?>empp-decimal.js"></script>
    <!-- Whole-row links in tables (tr[data-href]) -->
    <script src="<?= $_ENV['BASE_JS']; ?>empp-rows.js"></script>

    <!-- Main JS -->
    <script src="<?= $_ENV['BASE_JS']; ?>main.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"
      integrity="sha384-k5vbMeKHbxEZ0AEBTSdR7UjAgWCcUfrS8c0c5b2AfIh7olfhNkyCZYwOfzOQhauK" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"
      integrity="sha384-PgPBH0hy6DTJwu7pTf6bkRqPlf/+pjUBExpr/eIfzszlGYFlF9Wi9VTAJODPhgCO" crossorigin="anonymous"></script>

    
    
  </body>
</html>
