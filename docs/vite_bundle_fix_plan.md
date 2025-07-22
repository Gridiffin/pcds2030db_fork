# Vite Bundle Issue Analysis & Fix Plan

## 🚨 **Root Cause Identified: Bundle Name Mismatches**

During cleanup, we discovered that **agency pages are requesting Vite bundles that don't exist** or have wrong names.

---

## 📊 **Current Status Analysis**

### ✅ **Bundles That Exist:**
```
CSS Bundles:
- agency-dashboard.bundle.css ✅
- agency-programs-add-submission.bundle.css ✅  
- agency-programs-create.bundle.css ✅

JS Bundles:
- admin-dashboard.bundle.js ✅
- agency-dashboard.bundle.js ✅
- agency-initiatives.bundle.js ✅
- agency-programs-add-submission.bundle.js ✅
- agency-programs-create.bundle.js ✅
- agency-programs-view.bundle.js ✅
- login.bundle.js ✅
```

### ❌ **Bundles Requested But Missing:**
```php
// Pages requesting non-existent bundles:
$cssBundle = 'notifications';          // ❌ Missing
$jsBundle = 'notifications';           // ❌ Missing

$cssBundle = 'agency-reports';         // ❌ Missing  
$jsBundle = 'agency-reports';          // ❌ Missing

$cssBundle = 'outcomes';               // ❌ Missing
$jsBundle = 'outcomes';                // ❌ Missing

$cssBundle = 'initiatives';            // ❌ Missing CSS (JS exists)
$jsBundle = 'initiatives';             // ⚠️ Exists but misnamed

$cssBundle = 'agency-edit-program';    // ❌ Missing
$jsBundle = 'agency-edit-program';     // ❌ Missing
```

---

## 🔧 **Fix Strategy Options**

### **Option 1: Add Missing Bundles to Vite (Recommended)**
Update `vite.config.js` to generate the missing bundles:

```javascript
// Add to rollupOptions.input:
'agency-reports': 'assets/js/agency/reports/main.js',
'agency-notifications': 'assets/js/agency/users/notifications.js',
'agency-outcomes': 'assets/js/agency/outcomes/main.js',
'agency-edit-program': 'assets/js/agency/programs/edit.js',
'agency-initiatives': 'assets/js/agency/initiatives/view.js', // Already exists
```

### **Option 2: Update Pages to Use Existing Bundles**
Map pages to existing bundles or remove bundle requirements:

```php
// Fix bundle names in pages:
$cssBundle = 'agency-initiatives';     // Use existing
$jsBundle = 'agency-initiatives';      // Use existing

// Or remove bundles if not needed:
$cssBundle = null;
$jsBundle = null;
```

### **Option 3: Use Direct Asset Loading**
Replace Vite bundles with direct asset imports for simpler pages.

---

## 📋 **Recommended Action Plan**

### **Step 1: Quick Fix - Update Bundle Names**
Fix pages to use existing bundles where possible:

1. ✅ **Dashboard**: Fixed to `agency-dashboard`
2. 🔄 **Initiatives**: Change to `agency-initiatives`  
3. 🔄 **Programs**: Already using correct names
4. ⚠️ **Reports/Outcomes/Notifications**: Need new bundles or direct assets

### **Step 2: Add Missing Bundles to Vite**
Create entry points for missing functionality:
- Reports bundle
- Outcomes bundle  
- Notifications bundle
- Edit program bundle

### **Step 3: Rebuild Vite Bundles**
```bash
npm run build
```

### **Step 4: Verify Bundle Loading**
Test each agency page to confirm bundles load correctly.

---

## 🎯 **Immediate Fix Priority**

### **High Priority (Blocking functionality):**
1. **Dashboard** ✅ Fixed
2. **Programs** ✅ Already correct
3. **Initiatives** - Update to use existing `agency-initiatives`

### **Medium Priority (May use fallback assets):**
4. **Reports** - Create bundle or use direct assets
5. **Outcomes** - Create bundle or use direct assets
6. **Notifications** - Create bundle or use direct assets

---

**Should we proceed with the quick fixes first, then build the missing bundles?**

*Analysis Date: July 22, 2025*  
*Status: 🔧 **BUNDLE CONFIGURATION NEEDED***
