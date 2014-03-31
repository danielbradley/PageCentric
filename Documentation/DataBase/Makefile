all: dirs doco tables views fixed_data stored_procedures

dirs:
	mkdir -p _SQL/01Tables
	mkdir -p _SQL/02Views
	mkdir -p _SQL/03Data
	mkdir -p _SQL/04StoredProcedures
	mkdir -p _SQL/05Grants

doco:
#	max2tex --packages graphicx --out Specification _current/*.txt

tables:
	extract -s -p "tables"          _current/*.txt > _SQL/01Tables/tables.sql

views:
	extract -s -p "views"           _current/*.txt > _SQL/02Views/views.sql

fixed_data:
	extract -s -p "fixed_data"      _current/*.txt > _SQL/03Data/fixed_data.sql

stored_procedures:
	extract -s -p "sp"              _current/*.txt > _SQL/04StoredProcedures/sp.sql
