<?php
namespace Pincrowd\Model;

/**
 *
 *
 * @Document
 */
class User extends AbstractDomain
{
    /**
     * @var string
     * @Id(strategy="NONE")
     */
    protected $username;

    /**
     * @var string
     * @Field(type="string")
     */
    protected $firstname;

    /**
     * @var string
     * @Field(type="string")
     */
    protected $lastname;

    /**
     * @var array
     * @EmbedMany(targetDocument="Email")
     */
    protected $email = array();
    /**
     * @var array
     * @EmbedMany(targetDocument="Phonenumber")
     */
    protected $phonenumber = array();
}
