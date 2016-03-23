/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Object that handles RSA encryption and submission of the FE login form
 *
 * This is the same code as in the rsaauth extension, but assumes jquery is loaded.
 * It also returns a jquery promise from the
 */
var TYPO3FrontendLoginFormRsaEncryptionAjax = ( function( $ ) {

    var rsaFrontendLogin = function( form, publicKeyEndpointUrl ) {

        /**
         * Submitted form element
         */
        this.form = form;

        /**
         * XMLHttpRequest
         */
        this.xhr = null;

        /**
         * Endpoint URL to fetch the public key for encryption
         */
        this.publicKeyEndpointUrl = publicKeyEndpointUrl;

        /**
         * Field in which users enter their password
         */
        this.userPasswordField = form.pass;

        /**
         * Fetches a new public key by Ajax and encrypts the password for transmission
         *
         * @return {Promise} Returns a promise that is complete when the password is encryppted.
         */
        this.handleFormSubmitRequest = function() {
            var rsaFrontendLogin = this;
            var promise = $.ajax(
                this.publicKeyEndpointUrl
            ).done( function( response ) {
                rsaFrontendLogin.handlePublicKeyResponse( response, rsaFrontendLogin );
            });
            return promise;
        };

        /**
         * Parses the response and triggers submission of the form
         *
         * @param response Ajax response object
         * @param rsaFrontendLogin current processed object
         */
        this.handlePublicKeyResponse = function( response, rsaFrontendLogin ) {
            var publicKey = response.split( ':' );
            if ( publicKey[ 0 ] && publicKey[ 1 ] ) {
                rsaFrontendLogin.encryptPassword( publicKey[ 0 ], publicKey[ 1 ] );
            } else {
                alert( 'No public key could be generated. Please inform your TYPO3 administrator to check the OpenSSL settings.' );
            }
        };

        /**
         * Uses the public key with the RSA library to encrypt the password.
         *
         * @param publicKeyModulus
         * @param exponent
         */
        this.encryptPassword = function( publicKeyModulus, exponent ) {
            var rsa, encryptedPassword;

            rsa = new RSAKey();
            rsa.setPublic( publicKeyModulus, exponent );
            encryptedPassword = rsa.encrypt( this.userPasswordField.value );

            // Replace password value with encrypted password
            this.userPasswordField.value = 'rsa:' + hex2b64( encryptedPassword );
        };
    };

    /**
     * Encrypt password on submit
     *
     * @param form
     * @param publicKeyEndpointUrl
     * @return boolean
     */
    this.encrypt = function( form, publicKeyEndpointUrl ) {

        var formReady = $.Deferred();

        if ( !form.rsaFrontendLogin ) {
            form.rsaFrontendLogin = new rsaFrontendLogin( form, publicKeyEndpointUrl );
        }

        // If pass is not encrypted yet fetch public key and encrypt pass
        if ( !form.pass.value.match( /^rsa:/ ) ) {

            // When the password is encrypted, set the deferred object to done.
            form.rsaFrontendLogin.handleFormSubmitRequest().done( formReady.resolve );

        } else {

            // Pass is encrypted so form can be submitted
            formReady.resolve();
        }

        // Return a promise.
        return formReady.promise();
    };

    return this;

})( jQuery );
