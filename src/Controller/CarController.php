<?php

namespace App\Controller;


use App\Entity\Cars;
use App\Form\CarsType;
use App\Entity\Marques;
use App\Form\CarsModifyType;
use App\Repository\CarsRepository;
use App\Repository\ImagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class CarController extends AbstractController
{
    /**
     * Fonction pour afficher les différentes voitures
     *
     * @return Response
     */
    #[Route('/car', name: 'carspage')]
    public function index(CarsRepository $repo, Request $request): Response
    {
        $cars = $repo->findAll();
        return $this->render('car/index.html.twig', [
            'cars'=>$cars,
        ]);
    }
    /**
     * Nouvelle voiture
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/car/new", name:"newCar")]
    #[IsGranted("ROLE_ADMIN")]
    public function new(Request $request,EntityManagerInterface $manager):Response
    {
        $car= new Cars();
        $form = $this->createForm(CarsType::class,$car);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            foreach($car->getImages() as $image)
            {
                $image->setCars($car);
                $manager->persist($image);
            }

            $file = $form['cover']->getData();
            if(!empty($file))
            {
                $originalFilename=pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin;Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename."-".uniqid().".".$file->guessExtension();

                try{
                    $file->move(
                        $this->getParameter('upload_directory'),$newFilename
                    );
                }catch(FileException $e)
                {
                    return $e->getMessage();
                }
                $car->setCover($newFilename);

                $manager->persist($car);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Voiture <strong>{$car->getNom()}</strong> a bien été ajoutée "
                );
                return $this->redirectToRoute('carspage',[
                    'slug'=>$car->getSlug()
                ]);
            }
            }
            return $this->render('car/newCar.html.twig', [
                'car'=>$car,
                'form' => $form->createView()
            ]);  
    }

    /**
     * Modification de voiture
     */
    #[Route("/car/{slug}/update", name:"editCar")]
    #[Security("is_granted('ROLE_ADMIN')",message:"Ceci n'est pas votre annonce")]
    public function update(Request $request, EntityManagerInterface $manager,Cars $cars):Response
    {
        $fileName=$cars->getCover();
        if(!empty($filename))
        {
            new File($this->getParameter('upload_directory').'/'.$cars->getCover());
        }
        $form=$this->createForm(CarsModifyType::class,$cars);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $cars->setCover($fileName);
            $cars->setSlug('');
            $manager->persist($cars);
            $manager->flush();

            
            $this->addFlash(
                'success',
                "L'annonce <strong>{$cars->getNom()}</strong> a bien été modifiée"
            );

            return $this->redirectTOroute('carpage',['slug'=>$cars->getSlug()]);
        }
        return $this->renderForm("car/update.html.twig",[
            
            "form" => $form,
            "cars"=>$cars
        ]);
    }
    /**
     * Fonction pour afficher les voitures d'une marque
     */
    #[Route('/marques/{id}', name:'carmark')]
    public function allCarMark(Marques $marques,CarsRepository $repo):Response
    {
        $cars = $repo->findAll();
        return $this->render('car/markCars.html.twig',[
            'marques'=>$marques,
            'car'=>$cars,
        ]);
    }
    /**
     * Fonction pour la page d'info voiture
     */
    #[Route('/car/{slug}', name:'carpage')]
    public function carPage(string $slug,Cars $car,ImagesRepository $image):Response
    {
        $img = $image->findALl();

        return $this->render('car/car.html.twig',[
            'car'=>$car,
            'images'=>$img
        ]);
    }

    #[Route('/car/{slug}/delete', name:"carDelete")]
    #[Security("(is_granted('ROLE_ADMIN'))",message:'Cette annonce ne vous appartient pas')]
    public function delete(EntityManagerInterface $manager,Cars $cars):Response
    {
        if(!empty($cars->getCover()))
        {
            unlink($this->getParameter('upload_directory').'/'.$cars->getCover());
            $cars->setCover('');
            $manager->flush();
        }
        foreach($cars->getimages() as $image){
            $manager->remove($image);
        }
        $this->addFLash(
            'success',
            "La voiture <strong>{$cars->getNom()}</strong> a été supprimée"
        );
        $manager->remove($cars);
        $manager->flush();

        return $this->redirectToRoute('carspage');
    }
    
}
