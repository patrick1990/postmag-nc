# Postfix mail alias generator for Nextcloud

Postmag allows users to generate aliases for their email addresses so that they can use different email addresses for different services on the internet easily.

A configuration file for postfix that implements these aliases can be generated via the occ command line tool.

## Alias format

A generated alias is built out of 3 parts.

* alias_name: The user can choose an alias name to make it more easy for the user to identify the alias. It is recommended to use the name of the service where the alias will be used to identify compromised services if you recieve spam via this alias. For instance use the aliase_name "amazon" if you want to use the Alias for your Amazon account.
* alias_id: A random hexadecimal identifier is generated by Postmag. If the user thinks, that the alias is contained in a public email database in the internet, the user can lock this alias and generate a new one with the same alias_name. The new alias will have a new alias_id and can be replaced in the compromised service.
* user_alias_id: Every Nextcloud user will have an own random hexadecimal user_alias_id, that stays the same for every alias of the user. This way, there can be no alias conflicts between different users.

Every alias will be of the format

```
alias_name.alias_id.user_alias_id@your_domain.com
```

your_domian is configurable by the administrator.
