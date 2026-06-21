<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Orders: composite index for status filtering per restaurant (most queried pattern)
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasIndex('orders', 'orders_restaurant_status_created_index')) {
                $table->index(['restaurant_id', 'status', 'created_at'], 'orders_restaurant_status_created_index');
            }
            if (!Schema::hasIndex('orders', 'orders_restaurant_payment_status_index')) {
                $table->index(['restaurant_id', 'payment_status', 'created_at'], 'orders_restaurant_payment_status_index');
            }
        });

        // Notifications: for filtering unread + recent
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasIndex('notifications', 'notifications_read_created_index')) {
                $table->index(['read_at', 'created_at'], 'notifications_read_created_index');
            }
        });

        // Promo codes: for active/valid scope
        Schema::table('promo_codes', function (Blueprint $table) {
            if (!Schema::hasIndex('promo_codes', 'promo_codes_active_expires_index')) {
                $table->index(['is_active', 'expires_at'], 'promo_codes_active_expires_index');
            }
        });

        // Reservations: for customer lookup
        if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'customer_email')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (!Schema::hasIndex('reservations', 'reservations_customer_email_index')) {
                    $table->index('customer_email', 'reservations_customer_email_index');
                }
            });
        }

        // Activity log: for filtering by action and user
        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                if (!Schema::hasIndex('activity_log', 'activity_log_action_index') && Schema::hasColumn('activity_log', 'action')) {
                    $table->index('action', 'activity_log_action_index');
                }
                if (!Schema::hasIndex('activity_log', 'activity_log_user_created_index') && Schema::hasColumn('activity_log', 'user_id')) {
                    $table->index(['user_id', 'created_at'], 'activity_log_user_created_index');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_restaurant_status_created_index');
            $table->dropIndex('orders_restaurant_payment_status_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_read_created_index');
        });

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropIndex('promo_codes_active_expires_index');
        });

        if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'customer_email')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropIndex('reservations_customer_email_index');
            });
        }

        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                if (Schema::hasColumn('activity_log', 'action')) {
                    $table->dropIndex('activity_log_action_index');
                }
                if (Schema::hasColumn('activity_log', 'user_id')) {
                    $table->dropIndex('activity_log_user_created_index');
                }
            });
        }
    }
};
