<?php
namespace App\Controllers;

use App\Models\Setting;

class SettingController {
    private $model;

    public function __construct() {
        $this->model = new Setting();
    }

    public function index() {
        header("Content-Type: application/json");
        echo json_encode($this->model->get());
    }
}
