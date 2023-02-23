<?php

/*
 * Copyright (C) 2022 Giovanni Cascione <ing.cascione@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace MADkitchen\Module;

// Exit if accessed directly.
defined('ABSPATH') || exit;

class TimeTracker extends \MADkitchen\Modules\Module {

    protected $dependencies = [
        'BerlinDB',
        'w3css',
        'jQuery',
        'chartjs',
        'ett_activities',
    ];
    protected $autoload = true;
    protected $table_data = [
        'timetable' => [
            'schema' => "
			id  bigint(20) NOT NULL AUTO_INCREMENT,
			activity_id            bigint(20)   NOT NULL,
                        date_rec  DATE   NOT NULL,
                        user_group            bigint(20)   NOT NULL,
                        time_units            decimal(4,2)   NOT NULL,
                        user_name            bigint(20)   NOT NULL,
                        user_role            bigint(20)   NOT NULL,
                        job_tag            bigint(20)   NOT NULL,
			PRIMARY KEY (id)
			"
            ,
            'columns' => [
                //id
                'id' => [
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'extra' => 'auto_increment',
                    'primary' => true,
                    'sortable' => true,
                ],
                //activity_ID
                'activity_id' => [
                    'description' => 'Activities',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                    'relation' => 'activity_id',
                ],
                //date
                'date_rec' => [
                    'description' => 'Dates',
                    'type' => 'DATE',
                    'date_query' => true,
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //user_group
                'user_group' => [
                    'description' => 'User cluster',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                    'relation' => 'user_group',
                ],
                //time_units
                'time_units' => [
                    'description' => 'Time allocated',
                    'type' => 'DECIMAL',
                    'length' => '4,2',
                    'unsigned' => true,
                ],
                //user_name
                'user_name' => [
                    'description' => 'User name',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                    'relation' => 'user_name',
                ],
                //user_role
                'user_role' => [
                    'description' => 'User role',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                    'relation' => 'user_role',
                ],
                //job_tag
                'job_tag' => [
                    'description' => 'Job tags',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                    'relation' => 'job_wbs',
                ],
            ],
        ],
    ];
    protected $pages_data = [
        [
            'title' => 'Timetable',
            'slug' => 'tt/timetable',
        ],
        [
            'title' => 'Dashboard',
            'slug' => 'tt/dashboard',
        ],
        [
            'title' => 'Jobs setup',
            'slug' => 'tt/jobs',
        ],
        [
            'title' => 'Activities setup',
            'slug' => 'tt/activities',
        ],
        [
            'title' => 'Users setup',
            'slug' => 'tt/users',
        ],
    ];

    public function __construct() {

        $this->table_data = array_merge(
                $this->table_data,
                \MADkitchen\Database\TablesHandler::create_std_lookup_table('activity_id', "Activities", [
                    ['tag' => 'activity_group']
                ]),
                \MADkitchen\Database\TablesHandler::create_std_lookup_table('activity_group', "Group of activities"),
                \MADkitchen\Database\TablesHandler::create_std_lookup_table('job_no', "Job number"),
                \MADkitchen\Database\TablesHandler::create_std_lookup_table('job_wbs', "Job WBS", [
                    ['tag' => 'job_no']
                ]),
                \MADkitchen\Database\TablesHandler::create_std_lookup_table('job_tag', "Job tag", [['tag' => 'job_wbs']]),
                \MADkitchen\Database\TablesHandler::create_std_lookup_table('user_group', "Group of user"),
                \MADkitchen\Database\TablesHandler::create_std_lookup_table('user_role', "Role of users"),
                \MADkitchen\Database\TablesHandler::create_std_lookup_table('user_name', "Users", [
                    ['tag' => 'user_role'],
                    ['tag' => 'user_group'],
                    ['tag' => 'wp_id', 'relation' => false],
                    ['tag' => 'job_settings', 'type' => 'longtext', 'description' => 'Job specific settings', 'relation' => false]
                ]),
        );

        parent::__construct();
    }

}
