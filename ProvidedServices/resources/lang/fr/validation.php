<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'max' => [
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'array' => 'Le champ :attribute doit être un tableau.',
    'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'unique' => 'La valeur du champ :attribute existe déjà.',
    'email' => 'Le champ :attribute doit être une adresse email valide.',

    // Traduction des noms des champs pour éviter le nom brut dans les messages
    'attributes' => [
        'title' => 'titre',
        'description' => 'description',
        'skills' => 'compétences',
        'cv' => 'CV',
        'profile_picture' => 'photo de profil',
    ],
];
