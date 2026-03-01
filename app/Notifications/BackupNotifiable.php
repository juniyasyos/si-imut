<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class BackupNotifiable extends Model
{
    use Notifiable;

    protected $table = 'users';
    protected $fillable = [];

    /**
     * Create a notifiable instance for the admin user
     */
    public static function make()
    {
        // Get the first admin user or create a default one
        $adminUser = User::role(['super_admin', 'admin'])->first();

        if (!$adminUser) {
            // Fallback to first user if no admin found
            $adminUser = User::first();
        }

        if ($adminUser) {
            return $adminUser;
        }

        // If no users exist, create a dummy instance
        $instance = new static();
        $instance->exists = false;
        return $instance;
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail()
    {
        return config('backup.notifications.mail.to', 'admin@example.com');
    }

    /**
     * Route notifications for the database channel.
     */
    public function routeNotificationForDatabase()
    {
        // Return this model instance for database notifications
        return $this;
    }
}
