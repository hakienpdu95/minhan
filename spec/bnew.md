My Laravel CRM has a Branch module but the branches table is missing organization_id, 
which breaks TenantAwareModel (can't scope branches to a tenant/organization).
- Modules/Branch
Please:
1. Create a migration to add organization_id (unsignedBigInteger, nullable, 
   foreign key → organizations.id, after id column) to branches table
2. Update Branch model: add organization_id to $fillable, add belongsTo Organization 
   relationship, ensure TenantAwareModel trait works correctly
3. Check all Branch views (create/edit forms) — if organization_id select is missing, 
   add a <select> field listing all organizations; auto-select the authenticated user's 
   organization_id by default (Auth::user()->organization_id)
4. Update BranchController store/update methods to handle organization_id