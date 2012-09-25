<?php
namespace Pincrowd\Model;

/**
 * @package
 * @category
 * @subcategory
 */
/**
 * @package
 * @category
 * @subcategory
 *
 * @Document
 */
class Email extends AbstractDomain
{
    /**
     * @var string
     * @Field(type="string")
     */
    protected $label;
    /**
     * @var string
     * @Field(type="string")
     */
    protected $email;
}
