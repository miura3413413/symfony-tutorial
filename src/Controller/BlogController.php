<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    /**
    * @Route("/", name="blog_index")
     */
    public function indexAction(ManagerRegistry $doctrine): Response
    {
      // $em = $this->getDoctrine()->getManager();
      // $posts = $em->getRepository(Post::class)->findAll();
      $posts =  $doctrine->getRepository(Post::class)->findAll();
      return $this->render('blog/index.html.twig', [
        'posts' => $posts,
    ]);
    }

     /**
     * @Route("/{id}", name="blog_show", requirements={"id"="\d+"})
     */
    public function showAction(int $id, ManagerRegistry $doctrine): Response
    {
        // $em = $this->getDoctrine()->getManager();
        // $post = $em->getRepository(Post::class)->find($id);
        $post = $doctrine->getRepository(Post::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException('The post does not exist');
        }

        return $this->render('blog/show.html.twig', ['post' => $post]);
    }

     /**
     * @Route("/new", name="blog_new")
     */
    public function newAction(Request $request, ManagerRegistry $doctrine): Response
    {
        // フォームの組立
        $post = new Post();
        $form = $this->createFormBuilder($post)
            ->add('title')
            ->add('content')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
          // エンティティを永続化
          $post->setCreatedAt(new \DateTime());
          $post->setUpdateAt(new \DateTime());
          // $em = $this->getDoctrine()->getManager();
          $em = $doctrine->getManager();
          $em->persist($post);
          $em->flush();

          return $this->redirectToRoute('blog_index');
      }
        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

        /**
     * @Route("/{id}/edit", name="blog_edit", requirements={"id"="\d+"})
     */
    public function editAction(Request $request , int $id, ManagerRegistry $doctrine ): Response
    {
        $em = $doctrine->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id '.$id
            );
        }

        $form = $this->createFormBuilder($post)
            ->add('title')
            ->add('content')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // フォームから送信されてきた値と一緒に更新日時も更新して保存
            $post->setUpdatedAt(new \DateTime());
            $em->flush();

            return $this->redirectToRoute('blog_index');
        }

        // 新規作成するときと同じテンプレートを利用
        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("/{id}/delete", name="blog_delete", requirements={"id"="\d+"})
     */
    function deleteAction(int $id,  ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id '.$id
            );
        }
        // 削除
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('blog_index');
    }
    
}