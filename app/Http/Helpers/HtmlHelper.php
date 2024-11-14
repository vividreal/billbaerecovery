<?php

namespace App\Helpers;

class HtmlHelper
{
    public static function testMe()
    {
        return 'Test called';
    }
    public static function cropButton($value, $width, $height): string
    {
        return '<input type="file" name="' . $value . '" class="form-control form-control-lg mb-2 crop_file" data-name="' . $value . '" data-width="' . $width . '" data-height="' . $height . '">';
    }

    public static function editButton($path, $value = 'Edit')
    {
        $data = NULL;
        if ($path) {
            $data = '<a class="btn btn-sm btn-outline-dark btn-text-primary btn-hover-primary btn-icon mr-2" title="Edit" href="' . $path . '"><i class="fa fa-pencil-alt"></i></a>';
        }
        return $data;
    }
    public static function editOnClickButton($id, $value = 'Edit')
    {
        $data = NULL;
        if ($id) {
            $data = '<a class="btn btn-sm btn-outline-dark btn-text-primary btn-hover-primary btn-icon mr-2" title="Edit" href="javascript:;" onclick="manageModal('.$id.')"><i class="fa fa-pencil-alt"></i></a>';
        }
        return $data;
    }

    public static function deleteButton($id, $value = 'Delete')
    {
        $data = NULL;
        if ($id) {
            $data = '<a class="btn btn-sm btn-outline-dark btn-text-danger btn-hover-danger btn-icon mr-2" title="Delete" href="javascript:;" id="' . $id . '" onclick="ajaxDelete(this.id)"><i class="fa fa-times-circle"></i></a>';
        }
        return $data;
    }

    public static function restoreButton($id, $value = 'Active')
    {
        $data = NULL;
        if ($id) {
            $data = '<a class="btn btn-sm btn-outline-dark btn-text-success btn-hover-success btn-icon mr-2" title="Activate" href="javascript:;" id="' . $id . '" onclick="ajaxActivate(this.id)"><i class="fa fa-check-circle"></i></a>';
        }
        return $data;
    }
    public static function viewButton($path, $value = 'View')
    {
        $data = NULL;
        if ($path) {
            $data = '<a class="btn btn-sm btn-outline-dark btn-text-success btn-hover-success btn-icon mr-2" title="View" href="' . $path . '"><i class="fa fa-eye"></i></a>';
        }
        return $data;
    }

}

