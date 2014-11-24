<?php

use Martha\Plugin\GitHub\Authentication\Provider\GitHubAuthProvider;

describe('GitHubAuthProvider()', function () {
    beforeEach(function () {
        $this->plugin = $this->getProphet()->prophesize('Martha\Plugin\GitHub\Plugin');
        $this->provider = new GitHubAuthProvider($this->plugin->reveal(), []);
    });

    describe('->validateResult()', function () {
        it('should return false if no "code" is supposed in the request', function () {

        });
    });
});
