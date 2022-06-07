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
                        user_group            tinytext   NOT NULL,
                        time_units            decimal(4,2)   NOT NULL,
                        user_name            tinytext   NOT NULL,
                        user_role            tinytext   NOT NULL,
                        job_tag            bigint(20)   NOT NULL,
			PRIMARY KEY (id)
			"
            ,
            'columns' => [
                //id
                'id' => [
                    'name' => 'id',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'extra' => 'auto_increment',
                    'primary' => true,
                    'sortable' => true,
                ],
                //activity_ID
                'activity_id' => [
                    'name' => 'activity_id',
                    'description' => 'Activities',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                    'relation'=> 'activity_id',
                ],
                //date
                'date_rec' => [
                    'name' => 'date_rec',
                    'description' => 'Dates',
                    'type' => 'DATE',
                    'date_query' => true,
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //user_group
                'user_group' => [
                    'name' => 'user_group',
                    'description' => 'User cluster',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //time_units
                'time_units' => [
                    'name' => 'time_units',
                    'description' => 'Time allocated',
                    'type' => 'DECIMAL',
                    'length' => '4,2',
                    'unsigned' => true,
                //'searchable' => true,
                //'sortable'   => true,
                ],
                //user_name
                'user_name' => [
                    'name' => 'user_name',
                    'description' => 'User name',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //user_role
                'user_role' => [
                    'name' => 'user_role',
                    'description' => 'User role',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_tag
                'job_tag' => [
                    'name' => 'job_tag',
                    'description' => 'Job tags',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                    'relation'=> 'job_wbs',
                ],
            ],
        ],
        'activity_id' => [
            'schema' => "
			id  bigint(20) NOT NULL AUTO_INCREMENT,
			activity_id            tinytext   NOT NULL,
                        activity_id_name            tinytext   NOT NULL,
			activity_group            bigint(20)   NOT NULL,
                        activity_id_desc            tinytext   NOT NULL,
			PRIMARY KEY (id)
			"
            ,
            'columns' => [
                //id
                'id' => [
                    'name' => 'id',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'extra' => 'auto_increment',
                    'primary' => true,
                    'sortable' => true,
                ],
                //activity_id
                'activity_id' => [
                    'name' => 'activity_id',
                    'description' => 'Activity ID',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //activity_id
                'activity_id_name' => [
                    'name' => 'activity_id_name',
                    'description' => 'Activity name',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //activity_group
                'activity_group' => [
                    'name' => 'activity_group',
                    'description' => 'Activities group',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                    'relation'=> 'activity_group',
                ],
                //activity_desc
                'activity_id_desc' => [
                    'name' => 'activity_id_desc',
                    'description' => 'User cluster',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
            ],
        ],
        'activity_group' => [
            'schema' => "
			id  bigint(20) NOT NULL AUTO_INCREMENT,
			activity_group            tinytext   NOT NULL,
                        activity_group_name            tinytext   NOT NULL,
                        activity_group_desc            tinytext   NOT NULL,
			PRIMARY KEY (id)
			"
            ,
            'columns' => [
                //id
                'id' => [
                    'name' => 'id',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'extra' => 'auto_increment',
                    'primary' => true,
                    'sortable' => true,
                ],
                //activity_group
                'activity_group' => [
                    'name' => 'activity_group',
                    'description' => 'Activities',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //activity_group
                'activity_group_name' => [
                    'name' => 'activity_group_name',
                    'description' => 'Activities group',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //activity_desc
                'activity_group_desc' => [
                    'name' => 'activity_group_desc',
                    'description' => 'User cluster',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
            ],
        ],
        'job_no' => [
            'schema' => "
			id  bigint(20) NOT NULL AUTO_INCREMENT,
			job_no            tinytext   NOT NULL,
			job_name            tinytext   NOT NULL,
                        job_desc            tinytext   NOT NULL,
			PRIMARY KEY (id)
			"
            ,
            'columns' => [
                //job_no
                'job_no' => [
                    'name' => 'job_no',
                    'description' => 'Job number',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_wbs
                'job_name' => [
                    'name' => 'job_name',
                    'description' => 'Job WBS',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_tag
                'job_desc' => [
                    'name' => 'job_desc',
                    'description' => 'Job tags',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
            ],
        ],
        'job_wbs' => [
            'schema' => "
			id  bigint(20) NOT NULL AUTO_INCREMENT,
			job_wbs            tinytext   NOT NULL,
                        job_wbs_name            tinytext   NOT NULL,
			job_wbs_desc            tinytext   NOT NULL,
                        job_no            bigint(20)   NOT NULL,
			PRIMARY KEY (id)
			"
            ,
            'columns' => [
                //job_no
                'job_wbs' => [
                    'name' => 'job_wbs',
                    'description' => 'Job number',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_description
                'job_wbs_name' => [
                    'name' => 'job_wbs_name',
                    'description' => 'Job description',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_wbs_desc
                'job_wbs_desc' => [
                    'name' => 'job_wbs_desc',
                    'description' => 'Job description',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_no
                'job_no' => [
                    'name' => 'job_no',
                    'description' => 'Job description',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                    'relation'=> 'job_no',
                ],
            ],
        ],
        'job_tag' => [
            'schema' => "
			id  bigint(20) NOT NULL AUTO_INCREMENT,
			job_tag            tinytext   NOT NULL,
                        job_tag_name            tinytext   NOT NULL,
                        job_tag_description            tinytext   NOT NULL,
                        job_wbs            bigint(20)   NOT NULL,
			PRIMARY KEY (id)
			"
            ,
            'columns' => [
                //job_no
                'job_tag' => [
                    'name' => 'job_tag',
                    'description' => 'Job number',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_description
                'job_tag_name' => [
                    'name' => 'job_tag_name',
                    'description' => 'Job description',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_description
                'job_tag_desc' => [
                    'name' => 'job_tag_desc',
                    'description' => 'Job description',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_description
                'job_wbs' => [
                    'name' => 'job_wbs',
                    'description' => 'Job description',
                    'type' => 'bigint',
                    'length' => '20',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                    'relation'=> 'job_wbs',
                ],
            ],
        ],
        'user_group' => [
            'schema' => "
			id  bigint(20) NOT NULL AUTO_INCREMENT,
                        user_group            tinytext   NOT NULL,
			user_group_name            tinytext   NOT NULL,
                        user_group_desc            tinytext   NOT NULL,
			PRIMARY KEY (id)
			"
            ,
            'columns' => [
                //job_no
                'user_group' => [
                    'name' => 'user_group',
                    'description' => 'Job number',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_description
                'user_group_name' => [
                    'name' => 'user_group_name',
                    'description' => 'Job description',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_description
                'user_group_desc' => [
                    'name' => 'user_group_desc',
                    'description' => 'Job description',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
            ],
        ],
        'user_role' => [
            'schema' => "
			id  bigint(20) NOT NULL AUTO_INCREMENT,
                        user_role            tinytext   NOT NULL,
			user_role_name            tinytext   NOT NULL,
                        user_role_desc            tinytext   NOT NULL,
			PRIMARY KEY (id)
			"
            ,
            'columns' => [
                //job_no
                'user_role' => [
                    'name' => 'user_role',
                    'description' => 'Job number',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_description
                'user_role_name' => [
                    'name' => 'user_role_name',
                    'description' => 'Job description',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
                //job_description
                'user_role_desc' => [
                    'name' => 'user_role_desc',
                    'description' => 'Job description',
                    'type' => 'tinytext',
                    'unsigned' => true,
                    'searchable' => true,
                    'sortable' => true,
                ],
            ],
        ],
    ];
    protected $pages_data = [
        [
            'title' => 'Timesheets',
            'slug' => 'timesheets',
        ],
        [
            'title' => 'Timesheet IN',
            'slug' => 'timesheets/in',
        ],
        [
            'title' => 'Timesheet OUT',
            'slug' => 'timesheets/out',
        ],
    ];

}
