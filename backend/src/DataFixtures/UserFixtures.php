<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // V�rifier si l'utilisateur admin existe d�j�
        $existingAdmin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@admin.com']);
        
        if (!$existingAdmin) {
            // Cr�er l'utilisateur admin
            $admin = new User();
            $admin->setUsername('admin');
            $admin->setEmail('admin@admin.com');
            $admin->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            $admin->setAvatar('/img/avatar/6.png');
            $admin->setCreatedAt(new \DateTimeImmutable());
            
            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin');
            $admin->setPassword($hashedPassword);
            
            // Persister l'utilisateur
            $manager->persist($admin);
            
            echo "? Utilisateur admin créé avec succès\n";
        } else {
            echo "??  L'utilisateur admin existe déjà\n";
        }

        // Sauvegarder en base
        $manager->flush();
    }
}
