<?php
function dd(...$vars)
{
        $trace = debug_backtrace();
        $file = file($trace[0]['file']);

        echo '<style>pre {background-color: #000 !important; border-radius: 15px !important; color: #9efd2f !important; font-size: 12px !important; overflow: scroll !important;} .info {color: #3ec8e0 !important;}</style><pre><span class="info">File: ' . $trace[0]['file'] . '<br />Line: ' . $trace[0]['line'] . '</span><br />';

        foreach($vars as $key => $var):
            $varLine = $file[ $trace[0]['line'] - 1 ];
            preg_match_all("#\\$(\w+['->\[\]]*\w+[^);,]*)#", $varLine, $match);

            echo '<span class="info">Variable Name: ' . $match[0][$key] . '</span><br /><br />';

            var_dump($var);

            echo '<br /><span class="info">';
            for($i=0; $i<=60; $i++):
                echo '++';
            endfor;
            echo '</span><br />';
        endforeach;

        echo '</pre>';

}//end dd()