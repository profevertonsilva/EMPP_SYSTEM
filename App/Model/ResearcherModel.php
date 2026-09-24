<?php

namespace App\Model;

class ResearcherModel
{
    private $res_id;
    private $res_name;
    private $res_photo;
    private $res_institution;
    private $res_purpose;
    private $res_academic;
    private $fk_country_cou_id;
    private $fk_login_log_id;

    private $cou_id;
    private $cou_nicename;
    private $log_type;
    private $log_email;
    private $log_create;
    // Number of research studies, from ResearcherDAO::select()
    private $research_count;
    // Set by the profile update actions (not a column of researcher)
    private $res_birth;


    

    public function __set($nome, $valor)
    {
        $this->$nome = $valor;
    }

    public function __get($nome)
    {
        return $this->$nome;
    }

    // Public URL of the profile photo, or null when there is none.
    // Uploads land in administrator/ or researcher/ depending on the role at
    // upload time, so look in both: a researcher promoted to admin keeps the photo.
    public function photoUrl()
    {
        if (!$this->res_photo) {
            return null;
        }
        $file = basename($this->res_photo);
        foreach (['researcher', 'administrator'] as $folder) {
            if (is_file("resources/dashboard/assets/img/$folder/$file")) {
                return $_ENV['BASE_IMG'] . "$folder/" . rawurlencode($file);
            }
        }
        return null;
    }

    // First letter of the name, shown when there is no photo
    public function initial()
    {
        return mb_strtoupper(mb_substr((string) $this->res_name, 0, 1));
    }
}
