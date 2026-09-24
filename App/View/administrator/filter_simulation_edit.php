<!-- Content wrapper -->
<div class="content-wrapper ">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <!-- Contextual Classes -->
      <div class="card ">
        &nbsp;
        <div class="row">
          <div class="col-10">
            <h5 class="card-header"><?= $this->getView()->title; ?></h5>
          </div>
          <div class="col-2">
            <a href="/dashboard/researcher/research/view/<?= $this->getView()->ree_id; ?>"><button type="button" class="btn rounded-pill btn-primary"><i class="fa-solid fa-arrow-left menu-icon"></i>Back</button></a>
          </div>
        </div>
        
        <div class="container-xxl">
          <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
              <div class="card">
                <div class="card-body">
                  <form name="form_research" action="/dashboard/researcher/filterSimulationUpdate" method="POST">
                    <div class="text-center mb-3">
                      <div class="position-relative d-inline-block">

                        <label>Material</label>
                        <input type="text" class="form-control" name="fis_material" id="fis_material" value="<?= $this->getView()->filter_simulation->fis_material; ?>">
                        <label>Tickness, L(mm)</label>
                        <input type="text" class="form-control" name="fis_thickness" id="fis_thickness" value="<?= $this->getView()->filter_simulation->fis_thickness; ?>" required>
                        <label>Average fiber diameter, d<sub>f</sub>(&mu;m)</label>
                        <input type="text" class="form-control" name="fis_diameter" id="fis_diameter" value="<?= $this->getView()->filter_simulation->fis_diameter; ?>" required>
                        <label>Porosity, &epsilon; (-)</label>
                        <input type="text" class="form-control" name="fis_porosity" id="fis_porosity" value="<?= $this->getView()->filter_simulation->fis_porosity; ?>" required>
                        <label>Temperature, T(°C)</label>
                        <input type="text" class="form-control" name="fis_temperature" id="fis_temperature" value="<?= $this->getView()->filter_simulation->fis_temperature; ?>" required>
                        <label>Pressure, P(mmHg)</label>
                        <input type="text" class="form-control" name="fis_pressure" id="fis_pressure" value="<?= $this->getView()->filter_simulation->fis_pressure; ?>" required>
                        <label>Air face velocity, v<sub>s</sub>(m/s)</label>
                        <input type="text" class="form-control" name="fis_velocity" id="fis_velocity" value="<?= $this->getView()->filter_simulation->fis_velocity; ?>" required>
                        <label>Filter area (m<sup>2</sup>)</label>
                        <input type="text" class="form-control" name="fis_area" id="fis_area" value="<?= $this->getView()->filter_simulation->fis_area; ?>" required>
                        <label>Fiber density, &rho;<sub>f</sub>(kg/m<sup>3</sup>)</label>
                        <input type="text" class="form-control" name="fis_density" id="fis_density" value="<?= $this->getView()->filter_simulation->fis_density; ?>" required>
                        <label>Minimum particle size, d<sub>pi,min</sub>(&mu;m)</label><br>
                        <small class="text-muted">Minimum particle size in the distribution. Keep it as "zero".</small>
                        <input type="text" class="form-control" name="fis_size_min" id="fis_size_min" value="<?= $this->getView()->filter_simulation->fis_size_min; ?>" required>
                        <label>Maximum particle size, d<sub>pi,max</sub>(&mu;m)</label><br>
                        <small class="text-muted">Maximum particle size in the distribution. Keep it as "zero".</small>
                        <input type="text" class="form-control" name="fis_size_max" id="fis_size_max" value="<?= $this->getView()->filter_simulation->fis_size_max; ?>" required>
                        <label>Inlet dust concentration, C (mg/m<sup>3</sup>)</label>
                        <input type="text" class="form-control" name="fis_concentration" id="fis_concentration" value="<?= $this->getView()->filter_simulation->fis_concentration; ?>" required>
                        <label>Class</label>
                        <input type="text" class="form-control" name="fis_class1" id="fis_class1" value="100" disabled>
                        <input type="hidden" class="form-control" name="fis_class" id="fis_class" value="100">
                        
                        <input type="hidden" class="form-control" name="fk_research_ree_id" id="fk_research_ree_id" value="<?= $this->getView()->ree_id; ?>">
                        <hr>
                        <a href="/dashboard/researcher/research/view/<?= $this->getView()->ree_id; ?>"><button type="button" class="btn rounded-pill btn-primary"><i class="fa-solid fa-arrow-left menu-icon"></i>Cancel</button></a>
                        <button type="submit" class="btn rounded-pill btn-primary submit"><i class="fa-brands fa-searchengin menu-icon"></i>Get distribution</button>

                      </div>
                    </div>
                  </form>
                  
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

