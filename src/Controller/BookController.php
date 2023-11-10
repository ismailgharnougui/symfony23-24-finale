<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Form\BookType;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }
    
    #[Route('/bookadd', name: "add_book")]
    public function addBook(ManagerRegistry $doctrine, Request $req)
    {

        $book = new Book();
        $em = $doctrine->getManager();
        $frm = $this->createForm(BookType::class, $book);

        $frm->handleRequest($req);
        //$book->setPublished(true);

        if ($frm->isSubmitted()) {

            $nb =  $book->getAuthor()->getNbBooks() + 1;
            $book->getAuthor()->setNbBooks($nb);


            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute("read_books");
        }


        return $this->renderForm("book/addBook.html.twig", ["frm" => $frm]);
    }


    #[Route('/bookread', name: "read_books")]
    public function readBook(BookRepository $bookrepo)
    {
        $books = $bookrepo->findAll();
        return $this->render("book/readBook.html.twig", [
            "books" => $books,
        ]);
    }

    #[Route('/bookdelete/{ref}', name: "delete_book")]
    public function deleteBook($ref, BookRepository $bookrepo, ManagerRegistry $doctrine)
    {


        $em = $doctrine->getManager();
        $book = $bookrepo->find($ref);
        $nb =  $book->getAuthor()->getNbBooks() - 1;
        $book->getAuthor()->setNbBooks($nb);
        $em->remove($book);
        $em->flush();

        return $this->redirectToRoute("read_books");
    }


    #[Route('/bookupdate/{ref}', name: "update_book")]
    public function updateBook($ref, BookRepository $bookrepo, ManagerRegistry $doctrine, Request $req)
    {

        $book = $bookrepo->find($ref);
        $em = $doctrine->getManager();
        $frm = $this->createForm(BookType::class, $book);

        $frm->handleRequest($req);


        if ($frm->isSubmitted()) {

            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute("read_books");
        }


        return $this->renderForm("book/update.html.twig", ["frm" => $frm]);
    }

    #[Route('/bookdetails/{ref}', name: 'book_details')]
    public function bookDetails($ref, BookRepository $bookrepo, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $book = $bookrepo->find($ref);

        if (!$book) {
            echo "Author not found";
        }

        return $this->render('book/showBook.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/bookdeleteIfZero', name: "delete_if_zero")]
    public function deleteIfZero(AuthorRepository $authorrep, ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $authors = $authorrep->findBy(['nb_books' => 0]);
        foreach ($authors as $author) {
            $em->remove($author);
        }
        $em->flush();

        return $this->redirectToRoute("read_books");
    }

    
    #[Route('/booksearch', name: "search_books")]
    public function searchBookByRef(BookRepository $bookrepo, Request $req)
    {
        $ref = $req->get('ref');
        $books = $bookrepo->searchBookByRef($ref);
        return $this->render("book/readBook.html.twig", [
            "books" => $books,
        ]);
    }

    #[Route('/booktriTitle', name: "triTitle")]
    public function triTitle(BookRepository $rep)
    {
        $books = $rep->triTitle();
        return $this->render("book/readBook.html.twig", [
            "books" => $books,
        ]);
    }

    #[Route('/booktri', name: 'tri_book')]
    public function booksListByAuthors(BookRepository $bookrep): Response
    {
        $books = $bookrep->booksListByAuthors();

        return $this->render('book/readBook.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/bookshowOnly', name: 'showOnly')]
    public function showOnly(BookRepository $bookrep): Response
    {
        $books = $bookrep->findBooksBefore2023();

        return $this->render('book/readBook.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/bookupdateBooksCategory', name: 'updateBooksCategory')]
    public function updateBooksCategory(BookRepository $bookrep): Response
    {
        $books = $bookrep->updateBooksCategory();

        return $this->redirectToRoute("read_books");
    }

    #[Route('/bookcountRomance', name: 'countRomance')]
    public function countRomance(BookRepository $bookrep): Response
    {
        $booksCount = $bookrep->countRomance('Romance');

        return $this->render('book/count.html.twig', [
            'booksCount' => $booksCount,
        ]);
    }

    #[Route('/bookfindBooksPublishedBetween2014And2018', name: 'findBooksPublishedBetween2014And2018')]
    public function findBooksPublishedBetween2014And2018(BookRepository $bookrep): Response
    {
        $books = $bookrep->findBooksPublishedBetween2014And2018();

        return $this->render('book/readBook.html.twig', [
            'books' => $books,
        ]);
    }
/*
     #[Route('/book/search', name: "search_books")]
     public function searchBook(BookRepository $bookrepo, $ref, Request $req)
     {
        $books = new Book();
       $fr = $this->createForm(SearchType::class, $books);
      $fr->handleRequest($req);
         if ($fr->isSubmitted()) {
            $ref = $books->getRef();
                     $books = $bookrepo->searchQB($ref); 
            return $this->render("book/SearchBook.html.twig", [
                 "fr" => $fr->createView(),
                 "books" => $books,
             ]);
         }
        return $this->render("book/readBook.html.twig", [
             "books" => $books,
         ]);

     }
*/
}
