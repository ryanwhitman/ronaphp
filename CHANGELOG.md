### v. 1.001.0

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