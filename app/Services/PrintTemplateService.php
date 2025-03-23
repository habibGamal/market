<?php

namespace App\Services;
use App\PrintTemplate;
use Illuminate\Database\Eloquent\Model;

class PrintTemplateService
{
    public function printPage(Model $model)
    {
        if (!method_exists($model, 'printTemplate')) {
            throw new \Exception('Print template not found: please implement printTemplate method in your model');
        }
        $template =  $model->printTemplate();
        $template->validate();
        return view($template->getLayout(), ['template' => $template]);
    }
}
