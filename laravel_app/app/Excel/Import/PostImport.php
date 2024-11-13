<?php


namespace App\Excel\Import;

use Illuminate\Support\Str;

class PostImport extends ExcelModel
{
    protected $format = [
        'stt' => 'stt',
        'ma_bai_dang' => 'reference',
        'ten_menu' => 'category_name',
        'ten_sub_menu' => 'sub_category_name',
        'loai_bai_dang' => 'type', // 1: home, 2: community
        'trang_thai' => 'status',
        'ten_industry' => 'post_industry_name',
        'tieu_de' => 'title',
        'mo_ta' => 'description',
        'so_dien_thoai' => 'phone_number',
        'dia_chi_email' => 'email',
        'link_website' => 'website',
        'ten_shop' => 'store_name',
        'dia_chi_shop' => 'store_address',
        'longitude' => 'lng',
        'latitude' => 'lat',
        'khu_vuc' => 'store_area',
        // post jobs
//        'loai_job' => 'jb_type',
        'vi_tri_lam_viec' => 'work_position',
        'luong_trung_binh' => 'avg_salary',
        'luong_thap_nhat' => 'min_salary',
        'luong_cao_nhat' => 'max_salary',
        'loai_nhan_luong' => 'type_salary', // Full time, part time
        'loai_cong_viec' => 'job_type',
        'hop_dong_lao_dong' => 'job_contract',
        'thoi_gian_lam_viec' => 'job_time',
        'kinh_nghiem_lam_viec' => 'job_experience',
        'ky_nang_yeu_cau' => 'require_skill',
        'ky_nang_nang_cao' => 'advance_skill',
        'moi_truong_lam_viec' => 'job_environmental',
        // post sale
//        'loai_sale' => 'sale_type',
        'loai_hinh_kinh_doanh' => 'business_type',
        'ten_facebook' => 'facebook_name',
        'link_facebook' => 'facebook_url',
        'ten_instagram' => 'instagram_name',
        'link_instagram' => 'instagram_url',
        'co_so_vat_chat' => 'facilities',
        'so_luong_nhan_vien' => 'num_employees',
        'gia' => 'price',
        'hop_dong_cho_thue' => 'lease_agreement',
        'doanh_thu_trung_binh' => 'avg_revenue',
        'thong_tin_them' => 'additional_infor',
    ];

    protected $cast_format = [];

    protected $settings = [];

    public function __construct() {}

    public function validateTitle($data) {
        $check = true;
        $this->settings = [];
        foreach($data as $dat) {
            $key = Str::slug(trim($dat), '_');
            if(count($this->settings) < count($data)) {
                // process special case
                if(!isset($this->format[$key])) {
//					$check = false;
//					break;
                    array_push($this->settings, "NULL");
                } else {
                    array_push($this->settings, $this->format[$key]);
                }
            } else {
                break;
            }
        }
        return $check;
    }

    public function formatSingleModel($data) {
        $num_array = ['avg_salary', 'min_salary', 'max_salary',
            'price', 'num_employees', 'display_type', 'status', 'type',
            'avg_revenue', 'support', 'job_experience', 'type_salary'] ;
        $model = new PostImport();
        foreach($this->settings as $key=>$val) {
            if(!empty($val)) {
                $value = '';
                if(isset($data[$key])) {
                    if(in_array($val, $num_array)) {
                        $value = !isset($data[$key]) || empty($data[$key]) ? 0 : $data[$key];
                    } else {
                        $value = $data[$key];
                    }
                }
                $func = 'set'. ucwords($val);
                $model->$func($value);
            }
        }
        return $model;
    }

    public function formatModels($datas) {
        $response = [];
        foreach($datas as $key=>$data) {
            // remove title
            if($key >= 1) {
                array_push($response, $this->jsonSerialize($this->formatSingleModel($data)));
            }
        }
        return $response;
    }

    public function jsonSerialize($data): array {
        $json = [];
        foreach($this->settings as $val) {
            if(!empty($val)) {
                $func = 'get'. ucwords($val);
                if(isset($this->cast_format[$val])) {
                    $json[$this->cast_format[$val]] = $data->$func();
                } else {
                    $json[$val] = $data->$func();
                }
            }
        }
        return $json;
    }
}
