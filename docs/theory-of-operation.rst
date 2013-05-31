Theory of operation
===================

BaconAuthentication comes with an authentication service interface
(``BaconAuthentication\AuthenticationServiceInterface``), which defines two
methods. The first one being ``authenticate($request, $response)``, which
tries to authenticate the current request. This method is used for both
processing current input from the user (e.g. a login form), as well as
retreiving the currently authenticated subject. The return value of this method
will always be a :doc:`result object <result-object>`.

The other method is ``resetCredentials()``, which will simply remove all
persisted information and thus make the authenticated subject anonymous again.

