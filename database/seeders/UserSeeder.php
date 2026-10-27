<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Obtener / crear roles administrativos
        |--------------------------------------------------------------------------
        */

        $superadmin = Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Super administrador
        |--------------------------------------------------------------------------
        */

        $superadminUser = User::updateOrCreate(
            [
                'email' => 'ramon.morales41@gmail.com',
            ],
            [
                'name' => 'Ramón Morales',
                'password' => Hash::make('12345678'),
            ]
        );

        $this->ensureQrToken($superadminUser);

        $superadminUser->syncRoles([$superadmin]);

        /*
        |--------------------------------------------------------------------------
        | Administrador
        |--------------------------------------------------------------------------
        |
        | Cambia el nombre, correo y contraseña por los datos definitivos.
        |
        */

        $adminUser = User::updateOrCreate(
            [
                'email' => 'admin@industriashusky.com.mx',
            ],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin12345'),
            ]
        );

        $this->ensureQrToken($adminUser);

        $adminUser->syncRoles([$admin]);
    }

    /*
    |--------------------------------------------------------------------------
    | Generar QR token si el usuario no tiene uno
    |--------------------------------------------------------------------------
    */

    private function ensureQrToken(User $user): void
    {
        if (! $user->qr_token) {
            do {
                $token = Str::random(64);
            } while (
                User::where('qr_token', $token)->exists()
            );

            $user->update([
                'qr_token' => $token,
            ]);
        }
    }
}
