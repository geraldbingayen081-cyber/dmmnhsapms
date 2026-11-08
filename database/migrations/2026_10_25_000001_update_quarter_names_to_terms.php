<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update 1st Term
        DB::table('quarters')->where('sort_order', 1)->update([
            'name' => '1st Term',
            'short_name' => 'T1',
        ]);

        // Update 2nd Term
        DB::table('quarters')->where('sort_order', 2)->update([
            'name' => '2nd Term',
            'short_name' => 'T2',
        ]);

        // Update 3rd Term
        DB::table('quarters')->where('sort_order', 3)->update([
            'name' => '3rd Term',
            'short_name' => 'T3',
        ]);

        // Catch any remaining quarters with Quarter in name
        DB::table('quarters')->where('name', 'LIKE', '%Quarter%')->update([
            'name' => DB::raw("REPLACE(name, 'Quarter', 'Term')"),
        ]);

        if (DB::getSchemaBuilder()->hasTable('achievements')) {
            DB::table('achievements')->where('quarter', '1st Quarter')->orWhere('quarter', 'Q1')->update(['quarter' => '1st Term']);
            DB::table('achievements')->where('quarter', '2nd Quarter')->orWhere('quarter', 'Q2')->update(['quarter' => '2nd Term']);
            DB::table('achievements')->where('quarter', '3rd Quarter')->orWhere('quarter', 'Q3')->update(['quarter' => '3rd Term']);
            DB::table('achievements')->where('quarter', 'LIKE', '%Quarter%')->update([
                'quarter' => DB::raw("REPLACE(quarter, 'Quarter', 'Term')"),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('quarters')->where('sort_order', 1)->update([
            'name' => '1st Quarter',
            'short_name' => 'Q1',
        ]);

        DB::table('quarters')->where('sort_order', 2)->update([
            'name' => '2nd Quarter',
            'short_name' => 'Q2',
        ]);

        DB::table('quarters')->where('sort_order', 3)->update([
            'name' => '3rd Quarter',
            'short_name' => 'Q3',
        ]);

        DB::table('quarters')->where('name', 'LIKE', '%Term%')->update([
            'name' => DB::raw("REPLACE(name, 'Term', 'Quarter')"),
        ]);

        if (DB::getSchemaBuilder()->hasTable('achievements')) {
            DB::table('achievements')->where('quarter', '1st Term')->orWhere('quarter', 'T1')->update(['quarter' => '1st Quarter']);
            DB::table('achievements')->where('quarter', '2nd Term')->orWhere('quarter', 'T2')->update(['quarter' => '2nd Quarter']);
            DB::table('achievements')->where('quarter', '3rd Term')->orWhere('quarter', 'T3')->update(['quarter' => '3rd Quarter']);
        }
    }
};
