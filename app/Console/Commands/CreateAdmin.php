<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    // Le nom de la commande que tu taperas dans le terminal
    protected $signature = 'make:admin';

    protected $description = 'Crée un utilisateur administrateur pour Yield AI';

    public function handle()
    {
        $firstname = $this->ask('Prénom de l\'admin ?');
        $lastname  = $this->ask('Nom de l\'admin ?');
        $email     = $this->ask('Email ?');
        $password  = $this->secret('Mot de passe ?');

        // Vérification si l'utilisateur existe déjà
        if (User::where('email', $email)->exists()) {
            $this->error('Erreur : Cet email est déjà utilisé !');
            return;
        }

        $user = User::create([
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $email,
            'password'  => Hash::make($password),
            'role'      => 'admin', // Défini en dur ici, donc sécurisé
            'verified'  => true,    // On l'active automatiquement
            'ville'     => 'System',
            'phone'     => '0000000000'
        ]);

        $this->info("Succès ! L'administrateur {$email} a été créé.");
    }
}
