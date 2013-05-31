Authentication results
======================

.. php:namespace:: BaconAuthentication\Result

ResultInterface
---------------

Every call to the
:php:meth:`authenticate() <BaconAuthentication\\AuthenticationServiceInterface::authenticate()>`
method of the authentication service will return a result object. The returned
result is defined by the
:php:interface:`ResultInterface <BaconAuthentication\\Result\\ResultInterface>`:

.. php:interface:: ResultInterface

    Generic result interface.

.. php:method:: isSuccess()

    Returns whether the authentication was successful.

    :return: bool

.. php:method:: isFailure()

    Returns whether the authentication was a failure.

    :return: bool

.. php:method:: isChallenge()

    Returns whether the authentication generated a challenge.

    :return: bool

.. php:method:: getPayload()

    Returns the payload associated with the result.

    For a successful result, the payload should be the identity of the subject.
    In the case of a failure, it should contain error information enclosed in
    an :php:class:`Error <BaconAuthentication\\Result\\Error>` object. For a
    challenge, no payload is required.

    :return: mixed|null

Result
------

BaconAuthentication provides a generic implementation of the
:php:interface:`ResultInterface <BaconAuthentication\\Result\\ResultInterface>`,
which should be sufficient for most use-cases. It defines the following
additional methods:

.. php:class:: Result

    Generic result implementation.

.. php:const:: STATE_SUCCESS

    success

.. php:const:: STATE_FAILURE

    failure

.. php:const:: STATE_CHALLENGE

    challenge

.. php:method:: __construct($state[, $payload])

    :param string $state:
    :param mixed|null $payload:

Error
------

The :php:class:`Error <BaconAuthentication\\Result\\Error>` object which is
returned as payload in the case of a failure is defined like this:

.. php:class:: Error

.. php:method:: __construct($scope, $message)

    :param string $scope:
    :param string $message:

.. php:method:: getScope()

    :return: string

.. php:method:: getMessage()

    :return: string

