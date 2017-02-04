Change Log
==========

2017-02-03
----------

Changed:

 * Updated dependencies for Laravel 5.4 / Laravel-Doctrine
 * Updated DomainEventListener to better handle aggregate roots
   Previously LifeCycle events were used, however if an aggregate root was unmodified, the
   domain events might never be called. Using preFlush allows checking all managed entities
   and always firing domain events automatically.
 
2017-01-22
----------

 * Update to reflect changes in input mapper

2016-08-06
----------

Initial commit.
