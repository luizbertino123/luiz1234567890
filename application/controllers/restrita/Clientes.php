<?php

defined('BASEPATH') OR exit('Ação não permitida');

class Clientes extends CI_Controller {

    public function _construct() {
        parent::_construct();

        //Verificar se há sessão
    }

    public function index() {

        $data = array(
            'titulo' => 'Clientes cadastrados',
            'styles' => array(
                'bundles/datatables/datatables.min.css',
                'bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css'
            ),
            'scripts' => array(
                'bundles/datatables/datatables.min.js',
                'bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js',
                'bundles/jquery-ui/jquery-ui.min.js',
                'js/page/datatables.js',
            ),
            'clientes' => $this->core_model->get_all('clientes'),
        );

        // echo'<pre>';
        // print_r($data['clientes']);
        // exit();

        $this->load->view('restrita/layout/header', $data);
        $this->load->view('restrita/clientes/index');
        $this->load->view('restrita/layout/footer');
    }

    public function core($cliente_id = null) {

        $cliente_id = (int) $cliente_id;

        if (!$cliente_id) {

            //Cadastrar
        } else {

            //Editando...

            if (!$cliente = $this->core_model->get_by_id('clientes', array('cliente_id' => $cliente_id))) {
                $this->session->set_flashdata('erro', 'Cliente não encontrado');
                redirect('restrita/clientes');
            } else {

                //Sucesso...Cliente encontrado

                $this->form_validation->set_rules('cliente_nome', 'Nome', 'trim|required|min_length[4]|max_length[40]');
                $this->form_validation->set_rules('cliente_sobrenome', 'Sobrenome', 'trim|required|min_length[4]|max_length[140]');
                $this->form_validation->set_rules('cliente_data_nascimento', 'Data nascimento', 'trim|required');
                $this->form_validation->set_rules('cliente_cpf', 'CPF', 'trim|required|exact_length[14]|callback_valida_cpf');

                $cliente_telefone_fixo = $this->input->post('cliente_telefone_fixo');

                if ($cliente_telefone_fixo) {
                    $this->form_validation->set_rules('cliente_telefone_fixo', 'Telefone fixo', 'trim|exact_lenght[14]|callback_valida_telefone_fixo');
                }

                $this->form_validation->set_rules('cliente_telefone_movel', 'Celular', 'trim|required|min_length[14]|max_length[15]|callback_valida_telefone_movel');


                $this->form_validation->set_rules('cliente_cep', 'CEP', 'trim|required|exact_length[9]');
                $this->form_validation->set_rules('cliente_endereco', 'Endereço', 'trim|required|min_length[4]|max_length[140]');
                $this->form_validation->set_rules('cliente_numero_endereco', 'Numero', 'trim|max_length[15]');
                $this->form_validation->set_rules('cliente_bairro', 'Bairro', 'trim|min_length[3]|max_length[40]');
                $this->form_validation->set_rules('cliente_cidade', 'Cidade', 'trim|min_length[4]|max_length[100]');
                $this->form_validation->set_rules('cliente_estado', 'Estado', 'trim|exact_length[2]');
                $this->form_validation->set_rules('cliente_complemento', 'Complemento', 'trim|max_length[130]');





                if ($this->form_validation->run()) {

                    /*
                      [cliente_nome] => Lucio
                      [cliente_sobrenome] => Souza
                      [cliente_data_nascimento] => 2022-03-02
                      [cliente_cpf] => 377.374.060-34
                      [cliente_telefone_movel] => (23) 99999-9999
                      [cliente_telefone_fixo] =>
                      [cliente_cep] => 23970-000
                      [cliente_endereco] => Rua Do Bertinão
                      [cliente_numero_endereco] => 45
                      [cliente_bairro] => Centro
                      [cliente_cidade] => Curitiba
                      [cliente_estado] => PR
                      [cliente_complemento] =>

                     */
                    
                    $data = elements(
                            array(
                                'cliente_nome',
                                'cliente_sobrenome',
                                'cliente_data_nascimento',
                                'cliente_cpf',
                                'cliente_telefone_movel',
                                'cliente_cep',
                                'cliente_endereco',
                                'cliente_numero_endereco',
                                'cliente_bairro',
                                'cliente_cidade',
                                'cliente_estado',
                                'cliente_complemento',
                                
                            ),$this->input->post()
                            
                            );                  
                   
                } else {

                    //Erro de validação

                    $data = array(
                        'titulo' => 'Editar cliente',
                        'scripts' => array(
                            'mask/jquery.mask.min.js',
                            'mask/custom.js',
                        ),
                        'cliente' => $cliente,
                    );


                    $this->load->view('restrita/layout/header', $data);
                    $this->load->view('restrita/clientes/core');
                    $this->load->view('restrita/layout/footer');
                }
            }
        }
    }

    public function valida_cpf($cpf) {

        if ($this->input->post('cliente_id')) {

            $cliente_id = $this->input->post('cliente_id');

            if ($this->core_model->get_by_id('clientes', array('cliente_id !=' => $cliente_id, 'cliente_cpf' => $cpf))) {
                $this->form_validation->set_message('valida_cpf', 'O campo {field} já existe, ele deve ser único');
                return FALSE;
            }
        }

        $cpf = str_pad(preg_replace('/[^0-9]/', '', $cpf), 11, '0', STR_PAD_LEFT);
        // Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
        if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {

            $this->form_validation->set_message('valida_cpf', 'Por favor digite um CPF válido');
            return FALSE;
        } else {
            // Calcula os números para verificar se o CPF é verdadeiro
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf[$c] * (($t + 1) - $c); //Se PHP version < 7.4, $cpf{$c}
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf[$c] != $d) { //Se PHP version < 7.4, $cpf{$c}
                    $this->form_validation->set_message('valida_cpf', 'Por favor digite um CPF válido');
                    return FALSE;
                }
            }
            return TRUE;
        }
    }

    public function valida_telefone_fixo($cliente_telefone_fixo) {

        $cliente_id = $this->input->post('cliente_id');

        if (!$cliente_id) {

            //Cadastro

            if ($this->core_model->get_by_id('clientes', array('cliente_telefone_fixo' => $cliente_telefone_fixo))) {

                $this->form_validation->set_message('valida_telefone_fixo', 'Esse telefone já existe');
                return FALSE;
            } else {

                return true;
            }
        } else {

            //Edição

            if ($this->core_model->get_by_id('clientes', array('cliente_telefone_fixo' => $cliente_telefone_fixo, 'cliente_id !=' => $cliente_id))) {

                $this->form_validation->set_message('valida_telefone_fixo', 'Esse telefone já existe');
                return FALSE;
            } else {

                return true;
            }
        }
    }

    public function valida_telefone_movel($cliente_telefone_movel) {

        $cliente_id = $this->input->post('cliente_id');

        if (!$cliente_id) {

            //Cadastro

            if ($this->core_model->get_by_id('clientes', array('cliente_telefone_movel' => $cliente_telefone_movel))) {

                $this->form_validation->set_message('valida_telefone_movel', 'Esse telefone já existe');
                return FALSE;
            } else {

                return true;
            }
        } else {

            //Edição

            if ($this->core_model->get_by_id('clientes', array('cliente_telefone_movel' => $cliente_telefone_movel, 'cliente_id !=' => $cliente_id))) {

                $this->form_validation->set_message('valida_telefone_movel', 'Esse telefone já existe');
                return FALSE;
            } else {

                return true;
            }
        }
    }

}
