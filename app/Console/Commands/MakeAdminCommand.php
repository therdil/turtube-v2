<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdminCommand extends Command
{
    protected $signature = 'turtube:make-admin {email : Yönetici yapılacak kullanıcının e-posta adresi}';

    protected $description = 'Bir TurTube kullanıcısına yönetici yetkisi verir';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('Bu e-posta adresiyle kayıtlı kullanıcı bulunamadı.');

            return self::FAILURE;
        }

        $user->update(['is_admin' => true]);

        $this->info($user->email.' artık yönetici.');

        return self::SUCCESS;
    }
}
