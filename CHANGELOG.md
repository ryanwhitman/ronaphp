---------- v. .5.4.1 ----------

- Added docblock comments at the file level.

---------- v. .5.4.0 ----------

- The Procedure prep functionality will no longer insert a param with a NULL value into the processed input. Meaning, if the param wasn't passed in at all (and it wasn't required), the param failed the dependency checks, or it was explicitly passed in with a NULL value, then it will not exist in the processed input that gets fed to the Procedure itself.

- A new option was created for Procedure params - if "allow_empty_string" evaluates to true, the param is not required, and the param contains an empty string, the system will trim the value, bypass the filters, and insert it into the processed input. If the same scenario exists and "allow_empty_string" evaluates to false, the system will run the filters on the value. This solves the following 2 cases:
	1) The developer is updating a user record. If an email address is passed into the Procedure, they'll update the user's record. If an email address doesn't get passed in, they won't update the record at all. They DO NOT want an empty string to be passed in as the email address field. They would implicitly disallow empty strings.
	2) The developer is updating a user record. They are OK with 3 scenarios - they won't update the email address at all (if it isn't passed in), they'll update the email column with an actual email address, or they'll save an empty string into the email column. They would explicitly allow empty strings.

- A 400 Bad Request is now returned when the API authentication procedure prep functionality fails. Before, it was returning a 401 Unauthorized	error when anything failed related to the API authentication procedure.

- A 400 Bad Request is now returned when the API procedure itself fails. On success, it returns a 200 OK.

- A major bug was fixed with the way Procedures, Filters, and Controllers are stored. Before, the namespace was omitted during the saving / retrieval process. The only role the namespace played was that the tLoad method loaded the namespace file. "user.password.deactivate" saved to the same name as "user.deactivate"

- An Exception is now thrown when the tLoad method does not find a matching file for the provided namespace.

- Configurations 'rona.tload.namespace_delimiter' and 'rona.tload.file_delimiter' were created. Both now default as "." Setting the file_delimiter as "/" will enable folder nesting.

- App Routes no longer allow the singular version of the component. I.e. "view" is disallowed.

- The pre-built filter options and messages are now globally configurable, as well as the default failure message.

- API Route Authentication now happens with Api_::authenticate($set_auth_user_id_as). The 3rd and 4th params of Api::map() have been removed.

- API Route Authorization has been changed to Api_::authorize($procedure, $switches). $switches should be an assoc. array with 'newParam' => 'existingParam' format. Dot notation is also allowed to permit array handling.

---------- v. .5.3.0 ----------

- Changed directories: the directories for filters and procedures are now at the root level.
- Fixed the bug that was in the pre-built date filter. It was still returning test data on success.
- When a filter returns a failure and does not have a message, the Filter::run() method now provides a default error message. A lot of pre-built filters were changed to take advantage of this. "Response::set(false)"

---------- v. .5.2.0 ----------

Added reqd_param and opt_param methods to Procedure_ class
Added pre-built date filter (filter needs improvement)
Added pre-built string filter
Pre-built filters are now called by 'rona.[filter]' instead of 'rona.misc.[filter]'

---------- v. .5.1.0 ----------

- Overhauled the filter functionality. Added a "is_reqd" check prior to filter. All params now pass thru to the Procedure, including NULL & empty string values.
- A filter now receives the following args: $val, $label, $options
- Modified built-in filters to accommodate new pattern
- Deleted built-in filter "chars"
- Removed 'default' from default options of rona.misc.boolean filter
- Deleted the pre-built 'allpass' filter.
- Updated Helper::is_emptyString()
- Changed class name Api_Filter to Api_
- Renamed Procedure..->filter() to Procedure..->param()
- Deleted core/filters/util and moved set_val to Api_->set_param()
- The Response object no longer forces an array for 'data'
- improved Helper::array_set()
- pre-built filter "boolean" - 'return_tinyint' has been changed to 'return_int' and it now defaults to false
- The app can no longer hook into a Procedure on route execution. The ability to hook into the API is forthcoming and will be triggered by a hidden form field called "_api_endpoint".
- Changed Config('rona.header_input') to Config('rona.api.authentication.header_params')
- Added Helper::pluralize() & Helper::indefinite_article()

---------- v. .5.0.1 ----------

- added filter:rona.misc.emails
- added filter:rona.misc.numeric
- filter:rona.misc.alphanumeric no longer has a default field of "token"
- Changed filter:rona.misc.anything to filter:rona.util.allpass
- Changed filter:rona.user.name to filter:rona.misc.persons_name
- Changed filter:rona.user.password to filter:rona.misc.password and reworked

---------- v. .5.0.0 ----------

- Pre-built filters have been added to Core
- elliminated Api::*base_path functionality and added Config::('rona.api.paths', [])
- Added Api::no_route(), App::no_route(), and eliminated Route::no_route();
- added ->filter() to Api routes
- unfiltered input no longer gets passed to procedure
- changed locations of route files
- added config files for model and app
- changed is_alphanumeric_ci() to is_alphanumeric()
- reworked Helper::has_length_range()
- reworked Helper::get_random()
- Reworked the scope object/class
- changed Procedure::procedure() to Procedure::set() and Controller::controller() to Controller::set()
- changed Procedure::filter() to Filter::set() and Procedure::run_filter() to Filter::run()
- changed structure of folders / routes
- changed "prep" to "filter"
- procedure routes are now called in the api file
- Changed App class name to Rona
- Changed Rona::location() to Helper::location()
- Changed Rona::ret() to Response::set() & also converted to object
- Changed Rona::load_file() to Helper::load_file()
- Changed Helper::load_directories() to Helper::load_directory(), and also change some of the function arguments, and removed $_SERVER['DOCUMENT_ROOT']
- Changed Route::custom() to Route::map()
- Eliminated ability to pass in "requested route" to App::run()
	- Eliminated Request::reset()
- Eliminated all route id functionality
	- Eliminated ids in Route::get(), put, patch, etc.
	- Eliminated Route::get_routes_by_id()
	- Eliminated Route::path()
	- Eliminated Route::change()
- Procedures, controllers, etc. are now grouped and the groups only load when called.
- In App::run(), the Procedure now runs prior to the Controllers, and the Procedure return value is injected into Controllers.
- Added method override functionality for put, patch, etc.
- Routes now merge with existing routes of the same route instead of overwriting.