<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Department, DepartmentSpecialization};

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Department::truncate(); 
        
        DepartmentSpecialization::truncate();
        $departments = [
            [
                'name' => 'Internal Medicine',
                'description' => 'Deals with the prevention, diagnosis, and treatment of adult diseases.',
                'specializations' => ['Cardiology', 'Endocrinology', 'Gastroenterology', 'Pulmonology']
            ],
            [
                'name' => 'Surgery',
                'description' => 'Focuses on surgical treatments and procedures.',
                'specializations' => ['General Surgery', 'Neurosurgery', 'Orthopedic Surgery', 'Plastic Surgery']
            ],
            [
                'name' => 'Pediatrics',
                'description' => 'Specialized medical care for infants, children, and adolescents.',
                'specializations' => ['Neonatology', 'Pediatric Cardiology', 'Pediatric Neurology', 'Pediatric Pulmonology']
            ],
            [
                'name' => 'Obstetrics and Gynecology',
                'description' => 'Focuses on pregnancy, childbirth, and female reproductive health.',
                'specializations' => ['Maternal-Fetal Medicine', 'Gynecologic Oncology', 'Reproductive Endocrinology']
            ],
            [
                'name' => 'Radiology',
                'description' => 'Uses medical imaging for diagnosis and treatment.',
                'specializations' => ['Diagnostic Radiology', 'Interventional Radiology', 'Nuclear Medicine']
            ],
            [
                'name' => 'Billing and Finance',
                'description' => 'Manages financial transactions, insurance claims, and patient billing.',
                'specializations' => ['Insurance Processing', 'Patient Billing', 'Financial Counseling', 'Revenue Cycle Management']
            ],
            [
                'name' => 'Dermatology',
                'description' => 'Specializes in diagnosing and treating skin, hair, and nail conditions.',
                'specializations' => ['Medical Dermatology', 'Cosmetic Dermatology', 'Dermatopathology', 'Pediatric Dermatology']
            ],
            [
                'name' => 'Ophthalmology',
                'description' => 'Focuses on eye health and vision care.',
                'specializations' => ['Glaucoma', 'Cataract Surgery', 'Retinal Diseases', 'Corneal Disorders']
            ],
            [
                'name' => 'Physical Therapy',
                'description' => 'Provides rehabilitation services to improve mobility and manage pain.',
                'specializations' => ['Orthopedic Rehabilitation', 'Sports Therapy', 'Neurological Rehabilitation', 'Pediatric Therapy']
            ],
            [
                'name' => 'Mental Health',
                'description' => 'Provides counseling and treatment for mental health conditions.',
                'specializations' => ['Psychiatry', 'Psychology', 'Child & Adolescent Therapy', 'Addiction Counseling']
            ],
            [
                'name' => 'Laboratory Services',
                'description' => 'Conducts diagnostic tests and analyzes samples.',
                'specializations' => ['Clinical Pathology', 'Hematology', 'Microbiology', 'Clinical Chemistry']
            ],
            [
                'name' => 'Pharmacy',
                'description' => 'Manages and dispenses prescription medications.',
                'specializations' => ['Outpatient Pharmacy', 'Clinical Pharmacy', 'Medication Therapy Management']
            ]
        ];

        foreach ($departments as $data) {
            $department = Department::create([
                'name' => $data['name'],
                'description' => $data['description'],
            ]);

            foreach ($data['specializations'] as $specialization) {
                DepartmentSpecialization::create([
                    'department_id' => $department->id,
                    'name' => $specialization,
                ]);
            }
        }
    }
}