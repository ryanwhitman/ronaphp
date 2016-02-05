<?php

/* Build API routes here */

Api::set_base_path('/api/1.0');

Api::any('/test/{number}', 'rona.sample.test');

?>