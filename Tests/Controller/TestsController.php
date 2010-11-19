<?php

namespace Bundle\Everzet\BehatBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/*
 * This file is part of the EverzetBehatBundle.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * BehatBundle Test Actions Controller.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TestsController extends Controller
{
    public function pageAction($page)
    {
        return $this->render('Everzet\\BehatBundle\\Tests:Tests:page.php', array(
            'page' => preg_replace('/page(\d+)/', 'Page #\\1', $page)
        ));
    }

    public function redirectAction()
    {
        return $this->redirect($this->generateUrl('behat_tests_page', array('page' => 'page1')));
    }

    public function formAction()
    {
        return $this->render('Everzet\\BehatBundle\\Tests:Tests:form.php');
    }

    public function submitAction()
    {
        $data = $this->get('request')->request->all();

        return $this->render('Everzet\\BehatBundle\\Tests:Tests:submit.php', array(
            'method'        => $this->get('request')->getMethod()
          , 'name'          => $data['name']
          , 'age'           => $data['age']
          , 'speciality'    => $data['speciality']
        ));
    }
}
