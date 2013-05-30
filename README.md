BaconAuthentication
===================

Master:
[![Build Status](https://api.travis-ci.org/Bacon/BaconAuthentication.png?branch=master)](http://travis-ci.org/Bacon/BaconAuthentication)
[![Coverage Status](https://coveralls.io/repos/Bacon/BaconAuthentication/badge.png?branch=master)](https://coveralls.io/r/Bacon/BaconAuthentication)

Introduction
------------

BaconAuthentication is a general purpose authentication module for
Zend Framework 2. It comes with a pluggable authentication service which allows
to not only create simple username/password authentication, but also to easily
integrate third-party authentication (like OpenID or OAuth), as well as
two-factor authentication.

Installation
------------

1. Add Baconauthentication to your composer.json. Either use a stable tag for
   this or dev-master:

    ```json
    "require": {
        "bacon/bacon-authentication": "dev-master"
    }
    ```

2. Tell composer to download BaconAuthentication:

    ```bash
    $ php composer.phar update
    ```

Theory of operation
-------------------

BaconAuthentication comes with an authentication service interface
(```BaconAuthentication\AuthenticationServiceInterface```), which defines two
 methods. The first one being ```authenticate($request, $response)```, which
 tries to authenticate the current request. This method is used for both
 processing current input from the user (e.g. a login form), as well as
retreiving the currently authenticated subject. The return value of this method
 will always be a [Result object](#the-result-object).

The other method is ```resetCredentials()```, which will simply remove all
 persisted information and thus make the authenticated subject anonymous again.

### The Result object

The Result object ```BaconAuthentication\Result\Result``` implements the
 ```BaconAuthentication\Result\ResultInterface```. It comes with the following
 four methods to distinguish between different states:

- ```isSuccess()```: True when authentication was successful.
- ```isFailure()```: True when authentication failed.
- ```isChallenge()```: True when a challenge was generated. In this case you
                       should return the response object back to ZF2.
- ```getPayload()```: Returns the payload of the result. In case of a success,
                      it will be the identifier of the authenticated subject. In
                      the case of failure, it will contain an
                      [Error object](#the-error-object).

### The Error object

The Error object is used to carry error information. It contains two methods:

- ```getScope()```: Returns the scope of the error, which usually is the plugin
                    name which generated the error.
- ```getMessage()```: Returns the actual error message.

Pluggable authentication service
--------------------------------

A simple but yet powerful implementation of the authentication service interface
is shipped with BaconAuthentication. It allows you to add plug-ins to the
service which each fulfill specific tasks. These plug-ins are defined by the
following interfaces, all located in the ```BaconAuthentication\Plugin```
namespace:

- ```AuthenticationPluginInterface```: Receives credentials and tries to
                                       authenticate them.
- ```ChallengePluginInterface```: Creates a challenge when no authentication was
                                  possible.
- ```EventAwarePluginInterface```: Attaches itself to one or more events to
                                   fulfill its tasks.
- ```ExtractionPluginInterface```: Extracts credentials from a request, which
                                   are the passed to all authentication
                                   plug-ins.
- ```ResetPluginInterface```: Resets persisted authentication information.

Each plug-in may implement one or more of these interfaces. To add a plug-in to
 the service, pass it to the ```addPlugin($plugin, $priority = 1)``` method. By
 changing the priority, you can force earlier or later execution than of this
specific plug-in. For more information about the specific plug-in interfaces,
 please refer to their respective in-line documentation.

Beside those interfaces, BaconAuthentication also comes with the following
pre-defined plug-ins:

- [HttpBasicAuth](#http-basic-auth-plug-in)
- [HttpPost](#http-post-plug-in)
- [Session](#session-plug-in)

### HTTP Basic Auth plug-in

The basic auth plug-in takes care of extracting credentials from the HTTP
 headers, as well as triggering the credentials dialog when no credentials where
passed. This one should only be used in very simple applications and also used
with care, as there is no way for the application to let the browser forget the
 credentials.

### HTTP POST plug-in

The HTTP POST plug-in is responsible for extracting identity and password from a
 POST request. Beside usual extraction, it also accepts an InputFilter to
validate and filter the incoming data. It also takes a login form URL, which it
 will redirect to if no authentication information are available.

### Session plug-in

The session plug-in is responsible for storing a retrieved identifier in the
session and return it early in the authentication process. This one is a
 must-have for most HTTP-based applications.
