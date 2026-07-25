<?php

test('the home page redirects to the control centre', function () {
    $this->get('/')
        ->assertRedirect('/control-centre');
});
