<?php echo $page ?>

<ul>
    <li>
        <a href="<?php echo $view['router']->generate('behat_tests_page', array('page' => 'page10')) ?>">p10</a>
    </li>
    <li>
        <a href="<?php echo $view['router']->generate('behat_tests_page', array('page' => 'page0')) ?>">p0</a>
    </li>
    <li>
        <a href="<?php echo $view['router']->generate('behat_tests_page', array('page' => 'page22')) ?>">p22</a>
    </li>
</ul>

