<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Bảng quảng cáo link với user
        Schema::create('advert_advertisers', function (Blueprint $table) {
            $table->uuid('id')->primary('id');

            $table->string('name')->nullable(); // tên user
//            $table->string('contact')->nullable(); // người liên hệ
            $table->string('email')->nullable(); // địa chỉ
            $table->string('phone', 20)->nullable(); // điện thoại
            $table->text('notes')->nullable(); // ghi chú
            $table->tinyInteger('status')->default(0); // trạng thái
            $table->text('properties')->nullable(); // json
            $table->uuid('user_id')->index()->nullable(); // user đăng ký
//            $table->uuid('created_by')->nullable()->index(); // employee_id
//            $table->string('created_by_name')->nullable(); // tạo bởi
//            $table->uuid('updated_by')->nullable()->index();// employee_id
//            $table->string('updated_by_name')->nullable(); // cập nhật bởi

            $table->softDeletes();
            $table->timestamps();
        });
        // chiến dịch quảng cáo cho users
        Schema::create('advert_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary('id');

            $table->string('name')->nullable();
            $table->date('starts_at'); // ngày bắt đầu
            // ends_at is null when the campaign not expired
            $table->date('ends_at')->nullable(); // ngày kết thúc
            $table->integer('weight')->default(1); // độ ưu tiên
            $table->string('limit_type')->nullable();
            $table->integer('limit_per_day')->nullable(); // giới hạn trong ngày
            $table->text('notes')->nullable(); // ghi chú
            $table->tinyInteger('status')->default(0);

            $table->uuid('advertiser_id')->index(); // user id
            $table->text('properties')->nullable();

//            $table->uuid('created_by')->nullable()->index();
//            $table->string('created_by_name')->nullable();
//            $table->uuid('updated_by')->nullable()->index();
//            $table->string('updated_by_name')->nullable();

            $table->softDeletes();
            $table->timestamps();

//            $table->foreign('advertiser_id')
//                ->references('id')
//                ->on('advert_advertisers')
//                ->onDelete('cascade')
//                ->onUpdate('cascade');
        });
        // Bài banner quảng cáo
        Schema::create('advert_banners', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('name')->nullable();
            $table->string('dimension')->nullable();

            $table->string('type')->nullable();
            $table->text('content')->nullable();
            $table->integer('weight')->default(1);
            $table->string('url')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            $table->uuid('post_id')->index(); // post_id
            $table->uuid('campaign_id')->index();
            $table->text('properties')->nullable();

//            $table->uuid('created_by')->nullable()->index();
//            $table->string('created_by_name')->nullable();
//            $table->uuid('updated_by')->nullable()->index();
//            $table->string('updated_by_name')->nullable();

            $table->softDeletes();
            $table->timestamps();
//
//            $table->foreign('campaign_id')
//                ->references('id')
//                ->on('advert_campaigns')
//                ->onDelete('cascade')
//                ->onUpdate('cascade');
        });

        Schema::create('advert_websites', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('url')->nullable();
            $table->string('name')->unique();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('properties')->nullable();

//            $table->uuid('created_by')->nullable()->index();
//            $table->string('created_by_name')->nullable();
//            $table->uuid('updated_by')->nullable()->index();
//            $table->string('updated_by_name')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
        // 3 loại zone: zone boost, zone horizontal, zone popup
        Schema::create('advert_zones', function (Blueprint $table) {
            $table->uuid('id')->primary('id');

            $table->string('name')->unique();
            $table->string('key')->unique();
            $table->string('dimension')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('type')->default(1); // 1: boost, 2: horizontal, 3: popuo
            $table->tinyInteger('status')->default(1);
            $table->uuid('website_id')->index();
            $table->text('properties')->nullable();

//            $table->uuid('created_by')->nullable()->index();
//            $table->string('created_by_name')->nullable();
//            $table->uuid('updated_by')->nullable()->index();
//            $table->string('updated_by_name')->nullable();

            $table->softDeletes();
            $table->timestamps();

//            $table->foreign('website_id')
//                ->references('id')
//                ->on('advert_websites')
//                ->onDelete('cascade')
//                ->onUpdate('cascade');
        });
        // bai post va zone hien thi
        Schema::create('advert_banner_zone', function (Blueprint $table) {
            $table->uuid('banner_id')->index();
            $table->uuid('zone_id')->index();
            $table->text('properties')->nullable();

            $table->foreign('banner_id')
                ->references('id')
                ->on('advert_banners')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('zone_id')
                ->references('id')
                ->on('advert_zones')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
        // ghi nhận lượt click và view
        Schema::create('advert_impressions', function (Blueprint $table) {
            $table->uuid('id')->primary('id');

            $table->uuid('banner_id')->nullable();
            $table->uuid('zone_id')->nullable();
            $table->string('session_id')->nullable();

            $table->boolean('clicked')->default(false);
            $table->dateTime('time_viewed')->nullable();
            $table->dateTime('time_clicked')->nullable();

            $table->text('properties')->nullable();

//            $table->uuid('created_by')->nullable()->index();
//            $table->string('created_by_name')->nullable();
//            $table->uuid('updated_by')->nullable()->index();
//            $table->string('updated_by_name')->nullable();

            $table->softDeletes();
            $table->timestamps();

//            $table->foreign('banner_id')
//                ->references('id')
//                ->on('advert_banners')
//                ->onDelete('cascade')
//                ->onUpdate('cascade');
//
//            $table->foreign('zone_id')
//                ->references('id')
//                ->on('advert_zones')
//                ->onDelete('cascade')
//                ->onUpdate('cascade');
        });

        Schema::create('advert_imp_visitor_details', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->uuid('impression_id')->index();
            $table->uuid('user_id')->index()->nullable(); // visit boi ai

            $table->string('browser')->nullable();
            $table->string('browser_version')->nullable();
            $table->boolean('is_phone')->default(false);
            $table->boolean('is_tablet')->default(false);
            $table->boolean('is_desktop')->default(false);
            $table->boolean('is_robot')->default(false);
            $table->string('robot')->nullable();
            $table->string('device')->nullable();
            $table->string('platform')->nullable();
            $table->string('platform_version')->nullable();
            $table->text('languages')->nullable();
            $table->text('extras')->nullable();
            $table->text('properties')->nullable();

//            $table->uuid('created_by')->nullable()->index();
//            $table->string('created_by_name')->nullable();
//            $table->uuid('updated_by')->nullable()->index();
//            $table->string('updated_by_name')->nullable();

            $table->softDeletes();
            $table->timestamps();

//            $table->foreign('impression_id')
//                ->references('id')
//                ->on('advert_impressions')
//                ->onDelete('cascade')
//                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_banner_zone');
        Schema::dropIfExists('advert_imp_visitor_details');
        Schema::dropIfExists('advert_impressions');
        Schema::dropIfExists('advert_banners');
        Schema::dropIfExists('advert_zones');
        Schema::dropIfExists('advert_websites');
        Schema::dropIfExists('advert_campaigns');
        Schema::dropIfExists('advert_advertisers');
    }
};
