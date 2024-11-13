<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PostIndustry;
use App\Models\SubCategory;
use App\Repositories\CategoryRepository;
use App\Repositories\PostIndustryRepository;
use App\Repositories\SubCategoryRepository;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateDefaultPostIndustry extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create default category
        $repo_category = new CategoryRepository();
        $repo_sub_category = new SubCategoryRepository();
        $repo_industry = new PostIndustryRepository();

        DB::table('categories')->truncate();
        DB::table('sub_categories')->truncate();
        DB::table('post_industries')->truncate();

        $now = Carbon::now();
        $categories = $this->getCategory();
        $cat_inserts = [];
        $sub_cat_inserts = [];
        $post_industry_insert = [];
        $cat_i = 1;
        $indus_i = 1;
        foreach($categories as $type=>$cats) {
            foreach($cats as $name=>$subCats) {
                $cat_refix = config('enums.key_prefix.category') . $now->format(config('enums.key_prefix.format_date'));
                $id_cat = (string) Str::orderedUuid();
                $in_cats = [
                    'type' => $type,
                    'reference' => $cat_refix . str_pad($cat_i, 4, '0', STR_PAD_LEFT),
                    'id' => $id_cat,
                    'name' => $name,
                    'status' => Category::STATUS_ACTIVE,
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ];
                if(count($subCats) > 0) {
                    foreach($subCats as $subName => $sub) {
                        $subId = (string) Str::orderedUuid();
                        $in_subs = [
                            'id' => $subId,
                            'name' => $subName,
                            'category_id' => $id_cat,
                            'status' => SubCategory::STATUS_ACTIVE,
                            'created_at' => $now->toDateTimeString(),
                            'updated_at' => $now->toDateTimeString(),
                        ];
                        if(count($sub) > 0) {
                            foreach($sub as $n=>$d) {
                                $indus_refix = config('enums.key_prefix.post_industry') . $now->format(config('enums.key_prefix.format_date'));
                                $in_industry = [
                                    'id' => (string) Str::orderedUuid(),
                                    'reference' => $indus_refix . str_pad($indus_i, 4, '0', STR_PAD_LEFT),
                                    'sub_category_id' => $subId,
                                    'name' => $n,
                                    'description' => $d,
                                    'key' => Str::slug($subName . ' ' . $n, '_'),
                                    'status' => PostIndustry::STATUS_ACTIVE,
                                    'created_at' => $now->toDateTimeString(),
                                    'updated_at' => $now->toDateTimeString(),
                                ];
                                $indus_i ++;
                                array_push($post_industry_insert, $in_industry);
                            }
                        }
                        array_push($sub_cat_inserts, $in_subs);

                    }
                }
                array_push($cat_inserts, $in_cats);
                $cat_i ++;
            }
        }
        if(count($cat_inserts) > 0) {
            $repo_category->insertDBs($cat_inserts);
        }

        if(count($sub_cat_inserts) > 0) {
            $repo_sub_category->insertDBs($sub_cat_inserts);
        }

        if(count($post_industry_insert) > 0) {
            $repo_industry->insertDBs($post_industry_insert);
        }
    }

    private function getCategory() {
        return [
            Category::HOME_OWNER => [
                'Sales' => [
                    'Sell' => [
                        'Tiệm làm móng' => 'Cung cấp các dịch vụ như cắt, đánh bóng, vẽ móng và chăm sóc tay/chân',
                        'Nhà hàng' => 'Dịch vụ ăn uống, bao gồm thức ăn nhanh, nhà hàng gia đình và ăn uống cao cấp',
                        'Thẩm mỹ viện/Spa' => 'Cung cấp các dịch vụ làm đẹp toàn diện bao gồm chăm sóc da, chăm soc tóc và dịch vụ spa thư giãn',
                        'Tiệm hớt tóc' => 'Dịch vụ cắt tóc, chải chuốc và cạo râu dành cho nam giới',
                        'Quán cà phê' => 'Phục vụ đồ uống, bánh ngọt cho khách hàng có nhu cầu kinh doanh đồ uống',
                        'Cửa hàng bán lẻ' => 'Bán các sản phẩm như quần áo, mỹ phẩm hoặc các mặt hàng bán lẻ chuyển dụng',
                        'Dịch vụ di động' => 'Cung cấp các dịch vụ làm móng, làm tóc hoặc làm đjep tại các địa điểm hoặc sự kiện của khách hàng',
                        'Trung tâm chăm sóc sức khỏe và thể chất' => 'Yoga, Fitness, Massage và các dịch vụ liên quan đến sức khỏe khác',
                    ],
                    'Buy' => [
                        'Tiệm làm móng' => 'Cung cấp các dịch vụ như cắt, đánh bóng, vẽ móng và chăm sóc tay/chân',
                        'Nhà hàng' => 'Dịch vụ ăn uống, bao gồm thức ăn nhanh, nhà hàng gia đình và ăn uống cao cấp',
                        'Thẩm mỹ viện/Spa' => 'Cung cấp các dịch vụ làm đẹp toàn diện bao gồm chăm sóc da, chăm soc tóc và dịch vụ spa thư giãn',
                        'Tiệm hớt tóc' => 'Dịch vụ cắt tóc, chải chuốc và cạo râu dành cho nam giới',
                        'Quán cà phê' => 'Phục vụ đồ uống, bánh ngọt cho khách hàng có nhu cầu kinh doanh đồ uống',
                        'Cửa hàng bán lẻ' => 'Bán các sản phẩm như quần áo, mỹ phẩm hoặc các mặt hàng bán lẻ chuyển dụng',
                        'Dịch vụ di động' => 'Cung cấp các dịch vụ làm móng, làm tóc hoặc làm đjep tại các địa điểm hoặc sự kiện của khách hàng',
                        'Trung tâm chăm sóc sức khỏe và thể chất' => 'Yoga, Fitness, Massage và các dịch vụ liên quan đến sức khỏe khác',
                    ]
                ],
                'Jobs' => [
                    'Recruitment' => [],
                    'Find Job' => []
                ]
            ],
            Category::COMMUNITY => [
                'News' => []
            ]
        ];
    }
}
