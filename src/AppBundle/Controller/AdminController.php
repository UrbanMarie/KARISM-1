<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\Genre;
use AppBundle\Entity\Place;
use AppBundle\Entity\Role;
use AppBundle\Entity\Serie;
use AppBundle\Entity\Style;
use AppBundle\Entity\Technique;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of AdminController
 *
 * 
 * @Route("/admin")
 */
class AdminController extends Controller {

    /**
     * @Route("/demande", name="adminDemande")
     */
    public function adminDemandeValidation() {
        $this->get('session')->remove('serieDefault');
        $usersArtiste = $this->getDoctrine()->getRepository(User::class)->findByArtistValidate(0);
        $usersHote = $this->getDoctrine()->getRepository(User::class)->findByHoteValidate(0);
        return $this->render('admin/demandeValidation.html.twig', array('usersArtiste' => $usersArtiste, 'usersHote' => $usersHote));
    }

    /**
     * Retourne le Nombre d'utilisateurs demandant un new statue 
     * @Route("/get/users/request")
     */
    public function getUserRequestRole() {
        $nbr = 0;
        $usersArtiste = $this->getDoctrine()->getRepository(User::class)->findByArtistValidate(0);
        $usersHote = $this->getDoctrine()->getRepository(User::class)->findByHoteValidate(0);

        for ($i = 0; $i < count($usersArtiste); $i++) {
            $nbr = $nbr + 1;
        }

        for ($i = 0; $i < count($usersHote); $i++) {
            $nbr = $nbr + 1;
        }
        return new JsonResponse($nbr);
    }

    /**
     * Retourne le Nombre d'utilisateurs
     * @Route("/get/users/")
     */
    public function getUsers() {
        $nbr = 0;
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();


        for ($i = 0; $i < count($users); $i++) {
            $nbr = $nbr + 1;
        }


        return new JsonResponse($nbr);
    }

    /**
     * Retourne le Nombre de serie 
     * @Route("/get/series/")
     */
    public function getSerie() {
        $serieValid = array();
        $nbr = 0;

        $series = $this->getDoctrine()->getRepository(Serie::class)->findAll();
        $i = 0;
        while ($i < count($series)) {
            $checkValid = $series[$i]->getUserid()->getArtistValidate();
            if ($checkValid == null || 0) {
                $i++;
            } elseif ($checkValid == 1) {
                array_push($serieValid, $series[$i]);
                $i++;
            }
        }
        for ($i = 0; $i < count($serieValid); $i++) {
            $nbr = $nbr + 1;
        }



        return new JsonResponse($nbr);
    }

    /**
     * Retourne le Nombre de Place 
     * @Route("/get/places/")
     */
    public function getLieux() {
        $placeValid = array();
        $nbr = 0;

        $places = $this->getDoctrine()->getRepository(Place::class)->findAll();
        $i = 0;
        while ($i < count($places)) {
            $checkValid = $places[$i]->getFkUserid()->getHoteValidate();
            if ($checkValid == null || 0) {
                $i++;
            } elseif ($checkValid == 1) {
                array_push($placeValid, $places[$i]);
                $i++;
            }
        }
        for ($i = 0; $i < count($placeValid); $i++) {
            $nbr = $nbr + 1;
        }



        return new JsonResponse($nbr);
    }

    ////////////////////Validation demande de Role /////////////////////////
    ///////////Bounton reponse 

    /**
     * Validation postive dune demande re role ArtisteUser
     * @Route("/remote/user/{id}/artiste/valid")
     */
    public function remoteUserArtisteValid($id) {
        $em = $this->getDoctrine()->getManager();

        //Recuperation de l'entity roleArtiste
        $rolesArtistes = $this->getDoctrine()->getRepository(Role::class)->findByRole("ROLE_ARTISTE");
        $roleArtiste = $rolesArtistes[0];

        //Recuperation de l'entity User a valider 
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $listeRoleUser = $user->getRoles();
        array_push($listeRoleUser, $roleArtiste);
        $user->setRoles($listeRoleUser);
        $user->setArtistValidate(1);
        $em->merge($user);
        $em->flush($user);


        return new JsonResponse();
    }

    /**
     * Négative demande  role ArtisteUser
     * @Route("/remote/user/{id}/artiste/refuse")
     */
    public function remoteUserArtisteRefuse($id) {

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $user->setArtistValidate(null);

        $em = $this->getDoctrine()->getManager();
        $em->merge($user);
        $em->flush($user);


        return new JsonResponse();
    }

    /**
     * Postive demande role HoteUser
     * @Route("/remote/user/{id}/hote/valid")
     */
    public function remoteUserHoteValid($id) {
        $em = $this->getDoctrine()->getManager();

        //Recuperation de l'entity roleHote
        $rolesHote = $this->getDoctrine()->getRepository(Role::class)->findByRole("ROLE_HOTE");
        $roleHote = $rolesHote[0];

        //Recuperation de l'entity User a valider 
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $listeRoleUser = $user->getRoles();
        array_push($listeRoleUser, $roleHote);
        $user->setRoles($listeRoleUser);
        $user->setHoteValidate(1);
        $em->merge($user);
        $em->flush();

        return new JsonResponse();
    }

    /**
     * Négative demande  role ArtisteUser
     * @Route("/remote/user/{id}/hote/refuse")
     */
    public function remoteUserHoteRefuse($id) {

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $user->setHoteValidate(null);

        $em = $this->getDoctrine()->getManager();
        $em->merge($user);
        $em->flush($user);


        return new JsonResponse();
    }

    ///////Lien vers serie default de USER
    /**
     * @Route("/get/serie/default/user/{id}")
     */
    function getSerieDefaultUser($id) {

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $series = $this->getDoctrine()->getRepository(Serie::class)->findBy(array('name' => 'Default', 'userid' => $user));

        $this->get('session')->set('serieDefault', $series[0]);

        return $this->render('artiste/formCreateSerie.html.twig');
    }

    ///////Lien vers place default de USER
    /**
     * @Route("/get/place/default/user/{id}")
     */
    function getPlaceDefaultUser($id) {

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $place = $this->getDoctrine()->getRepository(Place::class)->findBy(array('name' => 'Default', 'fk_user' => $user));

        $listImg = $place[0]->getFk_ImagesPlace();

        return $this->render('hote/formCreatePlace.html.twig', array("places" => $place, "pictures" => $listImg));
    }

    ///////Gestion des styles

    /**
     * Affiche tout les styles 
     * @Route("/get/styles",name="getStyleAdmin")
     */
    public function getStyles() {

        $styles = $this->getDoctrine()->getRepository(Style::class)->findAll(array('name' => 'desc'));


        return $this->render('admin/gestionStyles.html.twig', array("styles" => $styles));
    }

    /**
     * @Route("create/style",name="createStyleAdmin")
     */
    public function createStyle(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $nom = $request->get('nomStyle');

        $styleForm = new Style();
        $styleForm->setName($nom);
        $em->persist($styleForm);
        $em->flush();
        return $this->redirect($this->generateUrl('getStyleAdmin'));
    }

    /**
     * @Route("/remove/style/{id}",name="removeStyleAdmin")
     */
    public function removeStyle($id) {
        $em = $this->getDoctrine()->getEntityManager();


        $style = $em->getRepository(Style::class)->find($id);

        $em->merge($style);

        $em->remove($style);
        $em->flush();
        return $this->redirect($this->generateUrl('getStyleAdmin'));
    }

    ///////Gestion des techniques

    /**
     * Affiche toutes les techniques 
     * @Route("/get/techs",name="getTechAdmin")
     */
    public function getTechs() {

        $techs = $this->getDoctrine()->getRepository(Technique::class)->findAll(array('name' => 'desc'));


        return $this->render('admin/gestionTechs.html.twig', array("techs" => $techs));
    }

    /**
     * @Route("create/tech",name="createTechAdmin")
     */
    public function createTech(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $nom = $request->get('nomTech');

        $techForm = new Technique();
        $techForm->setName($nom);
        $em->persist($techForm);
        $em->flush();
        return $this->redirect($this->generateUrl('getTechAdmin'));
    }

    /**
     * @Route("/remove/tech/{id}",name="removeTechAdmin")
     */
    public function removeTech($id) {
        $em = $this->getDoctrine()->getEntityManager();


        $tech = $em->getRepository(Technique::class)->find($id);

        $em->merge($tech);

        $em->remove($tech);
        $em->flush();
        return $this->redirect($this->generateUrl('getTechAdmin'));
    }

    ///////Gestion des genres

    /**
     * Affiche tous les genres 
     * @Route("/get/genres",name="getGenreAdmin")
     */
    public function getGenres() {

        $genres = $this->getDoctrine()->getRepository(Genre::class)->findAll(array('name' => 'desc'));


        return $this->render('admin/gestionGenres.html.twig', array("genres" => $genres));
    }

    /**
     * @Route("create/genre",name="createGenreAdmin")
     */
    public function createGenre(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $nom = $request->get('nomGenre');

        $genreForm = new Genre();
        $genreForm->setName($nom);
        $em->persist($genreForm);
        $em->flush();
        return $this->redirect($this->generateUrl('getGenreAdmin'));
    }

    /**
     * @Route("/remove/genre/{id}",name="removeGenreAdmin")
     */
    public function removeGenre($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $genre = $em->getRepository(Genre::class)->find($id);

        $em->merge($genre);

        $em->remove($genre);
        $em->flush();
        return $this->redirect($this->generateUrl('getGenreAdmin'));
    }

     ///////Gestion des villes
    
    /**
     * Affiche tous les genres 
     * @Route("/get/villes",name="getVillesAdmin")
     */
    public function getCity() {

        $villes = $this->getDoctrine()->getRepository(City::class)->findAll();


        return $this->render('admin/gestionVilles.html.twig', array("villes" => $villes));
    }

    /**
     * @Route("/create/ville",name="createVillesAdmin")
     */
    public function createCity(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $nom = $request->get('nomVilles');

        $villeForm = new City();
        $villeForm->setCity($nom);
        $em->persist($villeForm);
        $em->flush();
        return $this->redirect($this->generateUrl('getVillesAdmin'));
    }

    /**
     * @Route("remove/ville/{id}",name="removeVillesAdmin")
     */
    public function removeCity($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $ville = $em->getRepository(City::class)->find($id);

        $em->merge($ville);

        $em->remove($ville);
        $em->flush();
        return $this->redirect($this->generateUrl('getVillesAdmin'));
    }
    
}
