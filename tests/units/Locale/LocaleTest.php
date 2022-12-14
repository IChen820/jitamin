<?php

/*
 * This file is part of Jitamin.
 *
 * Copyright (C) Jitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../Base.php';

class LocaleTest extends Base
{
    public function testLocales()
    {
        foreach (glob('app/Locale/*') as $file) {
            $locale = require $file.'/translations.php';

            foreach ($locale as $k => $v) {
                if (strpos($k, '%B %e, %Y') !== false) {
                    continue;
                }

                if (strpos($k, '%b %e, %Y') !== false) {
                    continue;
                }

                foreach (['%s', '%d'] as $placeholder) {
                    $this->assertEquals(
                        substr_count($k, $placeholder),
                        substr_count($v, $placeholder),
                        'Incorrect number of '.$placeholder.' in '.basename($file).' translation of: '.$k
                    );
                }
            }
        }
    }
}
