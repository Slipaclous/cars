<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Cars;
use App\Entity\Images;
use App\Entity\Marques;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    //Hasher de password
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
          $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
      // Initialisation de faker pour les différentes fixtures
      $faker = Factory::create('fr_FR');


       //gestion des marques
       $nameMark = array(
        'Toyota','Alfa Romeo','Suzuki','Citroen','Audi','BMW','Mercedez-Benz','Honda',
       );
       $coverMark = array(
        'images/markCover/toyota.png','images/markCover/alfa-romeo.png','images/markCover/suzuki.png','images/markCover/citroen.png','images/markCover/audi.webp','images/markCover/bmw.png','images/markCover/mercedes.png','images/markCover/honda.png'
       );
       for($i=0;$i<8;$i++)
       {
        $marque = new Marques();
        $marque->setNom($nameMark[$i])
                ->setLogo($coverMark[$i]);


        for($v=0;$v <rand(1,5);$v++)
        {
          $cars = new Cars();
          $cylindree=[1.4,1.7,1.9];
          $carburant=['essence','diesel','LPG'];
          $transmission=['avant','arrière'];
          $cars->setNom($faker->word())
              ->setMarque($marque)
              ->setKilometrage(rand(1000,15000))
              ->setPrix(rand(1500,55000))
              ->setCylindree($cylindree[array_rand($cylindree)])
              ->setPuissance(rand(55,310))
              ->setCarburant($carburant[array_rand($carburant)])
              ->setTransmission($transmission[array_rand($transmission)])
              ->setAnneeCirculation($faker->dateTimeBetween($startDate='-30years',$endDate='now'))
              ->setNbProprio(rand(1,9))
              ->setDescription($faker->paragraph(1))
              ->setOptionCar($faker->paragraph(2))
              ->setCover('cover.webp');
              for($j=1;$j<3;$j++)
              {
                $image= new Images();
                $image->setImgCar('https://picsum.photos/200/200')
                      ->setCars($cars);
                      $manager->persist($image);
              }
              $manager->persist($cars);
          
        }
        $manager->persist($marque); 
       }
         

        $admin = new User();
        $admin->setEmail('cars@epse.be')
              ->setRoles(['ROLE_ADMIN'])
              ->setPassword($this->passwordHasher->hashPassword($admin,'password'));

        $manager->persist($admin);

        
        $manager->flush();
    }
}
