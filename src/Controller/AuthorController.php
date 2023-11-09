<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
    
class AuthorController extends AbstractController
{
    #[Route('/author/showAuthor/{name}', name: 'app_author')]
    public function showAuthor($name): Response
    {
        return $this->render('author/index.html.twig', [
            'name' => $name
        ]);
    }

    #[Route('/author/list', name: 'list_author')]
    public function list()
    {
        $authors = array(
            array('id' => 1, 'picture' => '/images/Victor-Hugo.jpg','username' => 'Victor Hugo', 'email' => 'victor.hugo@gmail.com ', 'nb_authors' => 100),
            array('id' => 2, 'picture' => '/images/william-shakespeare.jpg','username' => ' William Shakespeare', 'email' => ' william.shakespeare@gmail.com', 'nb_authors' => 200 ),
            array('id' => 3, 'picture' => '/images/Taha_Hussein.jpg','username' => 'Taha Hussein', 'email' => 'taha.hussein@gmail.com', 'nb_authors' => 300),
        );

        $totalauthors = array_reduce($authors, function ($carry, $author) {
            return $carry + $author['nb_authors'];
        }, 0);

        return $this->render('author/list.html.twig',[
            'authors' => $authors,
            'totalauthors' => $totalauthors,
        ]);
    }

    #[Route('/author/details/{id}', name: 'author_details')]
    public function authorDetails($id): Response
    {
        $author = $this->findAuthor($id);

        if (!$author) {
            echo "Author not found";
        }

        return $this->render('author/showAuthor.html.twig', [
            'author' => $author,
        ]);
    }


    private function findAuthor($id)
    {

        $authors = [
            ['id' => 1, 'picture' => '/images/Victor-Hugo.jpg', 'username' => 'Victor Hugo', 'email' => 'victor.hugo@gmail.com', 'nb_authors' => 100],
            ['id' => 2, 'picture' => '/images/william-shakespeare.jpg', 'username' => 'William Shakespeare', 'email' => 'william.shakespeare@gmail.com', 'nb_authors' => 200],
            ['id' => 3, 'picture' => '/images/Taha_Hussein.jpg', 'username' => 'Taha Hussein', 'email' => 'taha.hussein@gmail.com', 'nb_authors' => 300],
        ];
        foreach ($authors as $author) {
            if ($author['id'] == $id) {
                return $author;
            }
        }
        return null;
    }

    #[Route('/author/read', name: 'read_author')]
    public function read(AuthorRepository $authorrep): Response
    {
        $authors = $authorrep->findAll();

        return $this->render('author/read.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/author/add', name: 'add_author')]
    public function add(ManagerRegistry $doctrine, Request $request)
    {
        $em= $doctrine->getManager();
        $author = new Author();
        $frm = $this->createForm(AuthorType::class,$author);
        $frm->handleRequest($request);
        $author->setNbBooks(0);
        if ($frm->isSubmitted())
        {
            $em->persist($author);
            $em->flush();
            //return new response ("Object Added");
            return $this->redirectToRoute('read_author');
        }
        else
            return $this->renderForm('author/add.html.twig', [
                'frm' => $frm,
            ]);
    }

    #[Route('/author/delete/{id}', name: 'delete_author')]
    public function delete(ManagerRegistry $doctrine, AuthorRepository $authorrep, $id)
    {
        $em = $doctrine->getManager();
        $author = $authorrep->find($id);

        $books = $author->getBooks();
        foreach ($books as $book) {
            $em->remove($book);
        }
        $em->remove($author);
        $em->flush();

        return $this->redirectToRoute("read_author");

    }

    #[Route('/author/update/{id}', name: 'update_author')]
    public function update(ManagerRegistry $doctrine, AuthorRepository $authorrep, Request $request, $id)
    {
        $author = $authorrep->find($id);
        $em = $doctrine->getManager();
        $frm = $this->createForm(AuthorType::class, $author);

        $frm->handleRequest($request);
        if ($frm->isSubmitted()) {
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute("read_author");
        }


        return $this->renderForm("author/update.html.twig", ["frm" => $frm]);
    }  
    
    #[Route('/author/tri', name: 'tri_author')]
    public function listAuthorByEmail(AuthorRepository $authorrep): Response
    {
        $authors = $authorrep->listAuthorByEmail();

        return $this->render('author/read.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/author/findAuthorsByBookCount', name: 'findAuthorsByBookCount')]
    public function findAuthorsByBookCount(AuthorRepository $authorrep, Request $req): Response
    {
        $min = $req->get('min');
        $max = $req->get('max');
        $authors = $authorrep->findAuthorsByBookCount($min, $max);

        return $this->render('author/read.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/author/deleteAuthorsWithZeroBooks', name: 'deleteAuthorsWithZeroBooks')]
    public function deleteAuthorsWithZeroBooks(AuthorRepository $authorrep): Response
    {
        $authors = $authorrep->deleteAuthorsWithZeroBooks();
        return $this->redirectToRoute("read_author");
    }

}