### v. 1.0.0

- The pre-built Rona param filters were previously stored in a separate repo. That repo. has been deleted and the Rona module is now built into the RonaPHP framework.
- Several new param filters were added.
- Several obsolete Helper properties/methods were removed.
- Multiple filters can no longer be assigned to a param. The filter can now either be assigned as a string or array. If string, the immediate module will be used. If array, index 0 should be the module and index 1 should be the filter name.
- All options are now defined in a single array as argument 3 of ->opt_param() / ->reqd_param().