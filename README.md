## Service and User Management

To manage the services and users, the website employs a Content Management System (CMS). It is located in the folder `/cms`.

### Users

There are two levels of users: admin and member.

The admin user has total control over all services and all member users. The admin username is `cmsadmin` and the default password is `1234`, and the password can (and should) be changed to something else. The member user has total control over an existing service that is tied to that member account.

To creating a member user so an organization can modify their own service information, follow these steps:

1. The organization contact requests from the admin a user account for a service.
1. If the service does not yet exist, the admin chooses `Add a Service` and fills out any information on the service.
1. The admin then chooses `Create/Reset User` and selects the service.
1. The admin gives the organization contact the username and password.
1. The organization contact can then login via the CMS and modify their service information and change their password.

If the organization contact requests a new password from the admin because they lost or their password or their user account was compromised, they can follow steps above. They will not lose any existing information in their service.

