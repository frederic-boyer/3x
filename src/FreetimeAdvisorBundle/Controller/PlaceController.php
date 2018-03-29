<?php

namespace FreetimeAdvisorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FreetimeAdvisorBundle\Entity\Place;
use FreetimeAdvisorBundle\Entity\Advice;
use FreetimeAdvisorBundle\Entity\Photo;



class PlaceController extends Controller
{
  /**
  *
  * @Route("/place/index", name="place_index")
  * @Method("GET")
  */
  public function index()
  {
    $em = $this->getDoctrine()->getManager();
    $places = $em->getRepository('FreetimeAdvisorBundle:Place')->findby(array(),array('date' => 'desc'));
    return $this->render('@FreetimeAdvisorBundle/Resources/views/place/index.html.twig', array(
      'places' => $places,
    ));
  }

  /**
  * @Route("/new/place", name="new_place")
  * @Method({"GET","POST"})
  */
  public function newPlace(Request $request)
  {
    $user = $this->getUser();
    $user->getId();
    $place = new Place();
    $form = $this->createForm('FreetimeAdvisorBundle\Form\PlaceType', $place);
    $form->handleRequest($request);
    $place->setUser($user)
    ->setDate("now");
    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($place);
      $em->flush();

      return $this->redirectToRoute('new_place_advice', array('name' => $place->getName()));
    }
    return $this->render('@FreetimeAdvisorBundle/Resources/views/place/new.html.twig', array(
      'place' => $place,
      'user' => $user,
      'form' => $form->createView(),
    ));
  }


  /**
  * @Route("place/{name}", name="show_place")
  * @Method({"GET","POST"})
  */
  public function showPlace(Place $place, Request $request)
  {
    $advice = new Advice();
    $comment = $advice->getTitle();


    return $this->render('@FreetimeAdvisorBundle/Resources/views/place/show.html.twig', array(
      'place' => $place
    ));
  }

  /**
  * @Route("place/{name}/edit", name="edit_place")
  * @Method({"GET","POST"})
  */
  public function editPlace(Place $place, Request $request)
  {
    $editForm = $this->createForm('FreetimeAdvisorBundle\Form\PlaceType', $place);
    $editForm->handleRequest($request);
    if ($editForm->isSubmitted() && $editForm->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $place->setDate("now");
      $em->persist($place);
      $em->flush();

      return $this->redirectToRoute('show_place', array('name' => $place->getName()));
    }
    return $this->render('@FreetimeAdvisorBundle/Resources/views/place/edit.html.twig', array(
      'place' => $place,
      'edit_form' => $editForm->createView(),
    ));
  }

  /**
  * @Route("place/{name}/new/advice", name="new_place_advice")
  * @Method({"GET","POST"})
  */
  public function newPlaceAdvice(Place $place, Request $request)
  {
    $user = $this->getUser();
    $user->getId();
    $advice = new Advice();
    $form = $this->createForm('FreetimeAdvisorBundle\Form\AdviceType', $advice);
    $form->handleRequest($request);
    $advice
    ->setUser($user)
    ->setDate("now")
    ->setPlace($place);
    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($advice);
      $em->flush();

      return $this->redirectToRoute('new_place_photo', array('place_name' => $place->getName(),'advice_id' => $advice->getId()));
    }
    return $this->render('@FreetimeAdvisorBundle/Resources/views/advice/new.html.twig', array(
      'place'=> $place,
      'advice' => $advice,
      'user' => $user,
      'form' => $form->createView(),
    ));
  }

  /**
  * @Route("place/{place_name}/advice/{advice_id}/new/photo", name="new_place_photo")
  * @Template()
  * @ParamConverter("place", class="FreetimeAdvisorBundle:Place",options={"mapping":{"place_name":"name"}})
  * @ParamConverter("advice", class="FreetimeAdvisorBundle:Advice",options={"mapping":{"advice_id":"id"}})
  */
  public function newPlacePhoto( $place, $advice, Request $request)
  {
    // $advice = $this->getAdvice();
    $advice->getId();
    // dump($advice);
    $user = $this->getUser();
    $user->getId();
    $photo = new Photo();
    $form = $this->createForm('FreetimeAdvisorBundle\Form\PhotoType', $photo);
    $form->handleRequest($request);
    $photo
    ->setUser($user)
    ->setDate("now")
    ->setAdvice($advice)
    ->setPlace($place);
    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($photo);
      $em->flush();

      return $this->redirectToRoute('show_place', array('name' => $place->getName()));
    }
    return $this->render('@FreetimeAdvisorBundle/Resources/views/photo/new.html.twig', array(
      'place'=> $place,
      'user' => $user,
      'form' => $form->createView(),
    ));
  }

  /**
  * @Route("place/by/category/{category_name}", name="searchPlaceByCategory")
  * @Template()
  * @ParamConverter("category", class="FreetimeAdvisorBundle:Category",options={"mapping":{"category_name":"name"}})
  */
  public function searchPlaceByCategory($category)
  {
    $category->getName();
    $em = $this->getDoctrine()->getManager();
    $places = $em->getRepository('FreetimeAdvisorBundle:Place')->findby(array('category'=>$category),array('id' => 'desc'));
    return $this->render('@FreetimeAdvisorBundle/Resources/views/place/search/category/index.html.twig', array(
      'places' => $places,
    ));
  }

  /**
  * @Route("place/by/city/{city_name}", name="searchPlaceByCity")
  * @Template()
  * @ParamConverter("city", class="FreetimeAdvisorBundle:City",options={"mapping":{"city_name":"name"}})
  */
  public function searchPlaceByCity($city)
  {
    $city->getName();
    $em = $this->getDoctrine()->getManager();
    $places = $em->getRepository('FreetimeAdvisorBundle:Place')->findby(array('city'=>$city),array('id' => 'desc'));
    return $this->render('@FreetimeAdvisorBundle/Resources/views/place/search/category/index.html.twig', array(
      'places' => $places,
    ));
  }

}
