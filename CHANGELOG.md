### v. 1.5.0

- Adjustments to the stock app configuration, including the deletion of view_assets and the addition of file_locations. Review thoroughly before implementing.
- Added stock configuration for the module class.
- Added 404 no-route handlers to the stock Rona module.
- The HTTP Response handler now processes several options: full, module, and attrs. Previously, this parameter was passed in as $data and was just passed to the view_assets config., which has now been fully replaced.
- Improvements to RonaPHP Logger.
- The include_template_file method in the \Rona\Module class has been renamed to include_file.

### v. 1.4.0

- Deleted the existing `\Rona\Helper` class file and converted it to a resource housed in the `rona` module. The new helper resource consists of the exact same functionality but no longer uses static methods. The new helper resource is accessible via `->get_module_resource('rona', 'helper')`. All internal functionality, including the internal modules, has been updated to utilize the new resource.

### v. 1.3.1

- Adjusted format and content of RonaPHP Logger email report.
- Added "tag" to the RonaPHP Logger View Entry page.

### v. 1.3.0

- Added Rona Logger module.
- Added min/max options to the string filter.

### v. 1.2.0

- Added mysqli database resource.

### v. 1.1.0

- Previously, module components such as resources and routes were registered in the module's __construct() method. Now, all components except for the module's configuration are registered in a new init() method housed in the Rona class. The init() method is automatically executed in the run() method. This allows all modules to be registered before registering their components. To accommodate this change, several existing methods were changed from protected to public.
- A new locate_param_filter() method was added to the Rona class. This method finds a param filter by either its name or module/name combo.
- A new copy() method has been added to the Param_Filter_Group class that allows existing filters to be copied. Copying a filter allows the developer to maintain the existing callback, but modify the default options.
- Added editor config.

### v. 1.0.0

- The pre-built Rona param filters were previously stored in a separate repo. That repo. has been deleted and the Rona module is now built into the RonaPHP framework.
- Several new param filters were added.
- Several obsolete Helper properties/methods were removed.
- Multiple filters can no longer be assigned to a param. The filter can now either be assigned as a string or array. If string, the immediate module will be used. If array, index 0 should be the module and index 1 should be the filter name.
- All options are now defined in a single array as argument 3 of ->opt_param() / ->reqd_param().