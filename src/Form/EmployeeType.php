<?php

namespace App\Form;

use App\Entity\Employee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Full Name',
                'attr' => ['class' => 'block w-full border rounded p-2'],
            ])
            ->add('position', TextType::class, [
                'label' => 'Position',
                'attr' => ['class' => 'block w-full border rounded p-2'],
            ])
            ->add('salary', NumberType::class, [
                'label' => 'Salary',
                'attr' => ['class' => 'block w-full border rounded p-2'],
            ])
            ->add('hireDate', DateType::class, [
                'label' => 'Hire Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'block w-full border rounded p-2'],
            ])
            ->add('manager', NumberType::class, [
                'label' => 'Manager',
                'required' => false,
                'attr' => ['class' => 'block w-full border rounded p-2'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }
}
