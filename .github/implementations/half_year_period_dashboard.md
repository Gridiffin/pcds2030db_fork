# Feature: Half-Year Period Aggregation on Agency Dashboard

## Goal

When a user selects a "half year" period, the dashboard should aggregate and display data (programs, stats, charts, recent updates, outcomes, etc.) for both periods within that half year.

---

## TODO List

- [x] Analyze how periods and half-year selections are represented in the system
- [x] Update backend period selection logic to support half-year (returning multiple period IDs)
- [ ] Update DashboardController and data-fetching functions to aggregate data for multiple periods
- [ ] Update outcomes statistics to support multiple periods
- [ ] Update frontend period selector to allow half-year selection and pass correct value
- [ ] Update dashboard JS and PHP to handle/display aggregated data
- [ ] Test with different period selections (single, half-year, full year)
- [ ] Document new behavior and code changes
- [ ] Clean up and remove any test files

---

## Notes

- All database queries must be parameterized.
- Ensure code is modular and maintainable.
- Update documentation as needed.
- Suggest improvements if any inefficiencies are found during implementation.
