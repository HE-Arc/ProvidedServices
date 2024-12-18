<?php

return [
    // Règles génériques
    'required' => 'Le champ :attribute est obligatoire.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'max' => [
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'min' => [
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'array' => 'Le champ :attribute doit être un tableau.',
    'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'unique' => 'La valeur du champ :attribute existe déjà.',
    'email' => 'Le champ :attribute doit être une adresse email valide.',

    // Règles spécifiques aux champs
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',

    // Traduction des noms des champs
    'attributes' => [
        'title' => 'titre',
        'description' => 'description',
        'skills' => 'compétences',
        'cv' => 'CV',
        'profile_picture' => 'photo de profil',
        'email' => 'adresse e-mail',
        'password' => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe',
        'first_name' => 'prénom',
        'last_name' => 'nom',
        'gender' => 'genre',
        'role' => 'rôle',
    ],
];

