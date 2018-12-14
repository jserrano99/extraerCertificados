<?php

namespace RentaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class FormularioType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fecha', DateType::class, array (
                                    "label" => 'Fecha',
                                    "required" => false,
                                    'widget' => 'single_text',
                                    'attr' => array (
                                        'class' => 'form-control js-datepicker',
                                        'data-date-format' => 'dd-mm-yyyy',
                                        'data-class' => 'string',
                  )))
                ->add('organismo', EntityType::class, array(
                                    "label"=> 'Organismo',
                                    "class" => 'RentaBundle:Organismo',
                                    "placeholder" => "Seleccione Organismo.....",
                                    "required" => true,
                                    "attr" => array("class" => "form-control"))
                )
                ->add('descripcion', TextType::class, array (
                                    "label" => 'Descripción',
                                    "required" => 'true',
                                    "attr" => array ("class" => "form-control")))
                
                ->add('fichero', FileType::class,array(
                    'label' => "Fichero Certificados",
                    'data_class' => null,
                    "attr" =>array("class" => "form-control"),
                    'required' => true)
                )
                ->add('firma', FileType::class,array(
				"label" => "Fichero de Firma",
                                "attr" =>array("class" => "form-control"),
                                "data_class" => null,
                                "required" => true))
                ->add('texto', TextType::class, array (
                                    "label" => 'Texto',
                                    "required" => 'true',
                                    "attr" => array ("class" => "form-control")))
                ->add('Extraer', SubmitType::class, array(
                                    "attr" => array("class" => "btn btn-success")));
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RentaBundle\Entity\Formulario'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'rentabundle_formulario';
    }


}
