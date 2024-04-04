<?php
namespace NFService\PlugNotas;

use CURLFile;

/**
 * Classe Tools
 *
 * Classe responsável pela comunicação com a API PlugNotas da Tecnospeed
 *
 * @category  NFService
 * @package   NFService\PlugNotas\Tools
 * @author    Jefferson Moreira <jeematheus at gmail dot com>
 * @copyright 2022 NFSERVICE
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Tools
{
    /**
     * Armazena o token que será usado para autenticação com a API
     *
     * @var string
     */
    private $authToken;

    /**
     * Armazena se a requisição é um upload ou não
     * Após feita a requisição esse valor é setado com o valor inicial
     *
     * @var bool
     */
    private $isUpload = false;

    /**
     * Armazena se a requisição é em produção ou não
     *
     * @var bool
     */
    private $isProduction = true;

    /**
     * Metodo contrutor da classe
     *
     * @param string $authToken Token de autenticação
     */
    public function __construct(string $authToken)
    {
        $this->authToken = $authToken;
    }

    /**
     * Altera o ambiente de comunicação
     *
     * @param bool $production Indica se é ambiente de produção ou não
     *
     * @access public
     * @return void
     */
    public function setProduction(bool $production)
    {
        $this->isProduction = $production;
    }


    /**
     * Realiza o envio de 1 ou até 5000 NFe para o Plugnotas
     *
     * @param array $dataNfes Array contendo as NFes já no padrão Plugnotas
     * @return array
     */
    public function enviaNfe(array $dataNfes) :array
    {
        if (empty($dataNfes)) {
            throw new \Exception("Nenhuma NFe informada");
        }

        if (count($dataNfes) > 5000) {
            throw new \Exception("Máximo de 5000 NFes por chamada");
        }

        $result = $this->post('/nfe', $dataNfes);
        return $result;
    }

    /**
     * Realiza o envio de 1 ou até 5000 NFCe para o Plugnotas
     *
     * @param array $dataNfces Array contendo as NFes já no padrão Plugnotas
     * @return array
     */
    public function enviaNfce(array $dataNfces) :array
    {
        if (empty($dataNfces)) {
            throw new \Exception("Nenhuma NFCe informada");
        }

        if (count($dataNfces) > 5000) {
            throw new \Exception("Máximo de 5000 NFCes por chamada");
        }

        $result = $this->post('/nfce', $dataNfces);
        return $result;
    }

    /**
     * Realiza o envio de 1 ou até 5000 NFSe para o Plugnotas
     *
     * @param array $dataNfses Array contendo as NFSes já no padrão Plugnotas
     * @return array
     */
    public function enviaNfse(array $dataNfses) :array
    {
        if (empty($dataNfses)) {
            throw new \Exception("Nenhuma NFSe informada");
        }

        if (count($dataNfses) > 5000) {
            throw new \Exception("Máximo de 5000 NFSes por chamada");
        }

        $result = $this->post('/nfse', $dataNfses);
        return $result;
    }

    /**
     * Realiza a consulta de uma NFe no PlugNotas a partir do campo idIntegracao
     *
     * @param string $emitente CNPJ do emitente sem formtação
     * @param string $idIntegracao Id de Integração da NFe
     * @return array
     */
    public function resumoNfeIntegracao(string $emitente, string $idIntegracao) :array
    {
        $result = $this->get("/nfe/$emitente/$idIntegracao/resumo");
        return $result;
    }

    /**
     * Realiza a consulta de uma NFCe no PlugNotas a partir do campo idIntegracao
     *
     * @param string $emitente CNPJ do emitente sem formtação
     * @param string $idIntegracao Id de Integração da NFCe
     * @return array
     */
    public function resumoNfceIntegracao(string $emitente, string $idIntegracao) :array
    {
        $result = $this->get("/nfce/$emitente/$idIntegracao/resumo");
        return $result;
    }

    /**
     * Realiza a consulta de uma NFSe no PlugNotas a partir do campo idIntegracao
     *
     * @param string $emitente CNPJ do prestador sem formatação
     * @param string $idIntegracao ID de Integração da NFSe no PlugNotas
     * @return array
     */
    public function resumoNfseIntegracao(string $prestador, string $idIntegracao) :array
    {
        $result = $this->get("/nfse/consultar/$idIntegracao/$prestador");
        return $result;
    }

    /**
     * Função responsável por buscar o XML de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @return array
     */
    public function xmlNfe(string $idPlugNotas) :array
    {
        $result = $this->get("/nfe/$idPlugNotas/xml", ["tipo" => "autorizacao"]);
        return $result;
    }

    /**
     * Função responsável por buscar o XML de uma NFCe
     *
     * @param string $idPlugNotas ID da NFCe no PlugNotas
     * @return array
     */
    public function xmlNfce(string $idPlugNotas) :array
    {
        $result = $this->get("/nfce/$idPlugNotas/xml", ["tipo" => "autorizacao"]);
        return $result;
    }

    /**
     * Função responsável por buscar o XML de uma NFSe (usado para xml autorizado e cancelado)
     *
     * @param string $idPlugNotas ID da NFSe no PlugNotas
     * @return array
     */
    public function xmlNfse(string $idPlugNotas) :array
    {
        $result = $this->get("/nfse/xml/$idPlugNotas");
        return $result;
    }

    /**
     * Função responsável por buscar o PDF de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @return array
     */
    public function pdfNfe(string $idPlugNotas) :array
    {
        $result = $this->get("/nfe/$idPlugNotas/pdf");
        return $result;
    }

    /**
     * Função responsável por buscar o PDF de uma NFCe
     *
     * @param string $idPlugNotas ID da NFCe no PlugNotas
     * @return array
     */
    public function pdfNfce(string $idPlugNotas) :array
    {
        $result = $this->get("/nfce/$idPlugNotas/pdf");
        return $result;
    }

    /**
     * Função responsável por buscar o PDF (DANFSe) de uma NFSe (usado para pdf autorizado e cancelado)
     *
     * @param string $idPlugNotas ID da NFSe no PlugNotas
     * @return array
     */
    public function pdfNfse(string $idPlugNotas) :array
    {
        $result = $this->get("/nfse/pdf/$idPlugNotas");
        return $result;
    }

    /**
     * Função responsável por solicitar a correção de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @param string $correcao Texto de correção
     * @return array
     */
    public function correcaoNfe(string $idPlugNotas, string $correcao) :array
    {
        $result = $this->post("/nfe/$idPlugNotas/cce", [
            'correcao' => $correcao
        ]);
        return $result;
    }

    /**
     * Função responsável por consultar a correção de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @return array
     */
    public function consultaCorrecaoNfe(string $idPlugNotas) :array
    {
        $result = $this->get("/nfe/$idPlugNotas/cce/status");
        return $result;
    }

    /**
     * Função responsável por buscar o XML da correção de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @return array
     */
    public function xmlCorrecaoNfe(string $idPlugNotas) :array
    {
        $result = $this->get("/nfe/$idPlugNotas/cce/xml");
        return $result;
    }

    /**
     * Função responsável por buscar o PDF da correção de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @return array
     */
    public function pdfCorrecaoNfe(string $idPlugNotas) :array
    {
        $result = $this->get("/nfe/$idPlugNotas/cce/pdf");
        return $result;
    }

    /**
     * Função responsável por solicitar a justificativa de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @param string $justificativa Texto de justificativa
     * @return array
     */
    public function cancelamentoNfe(string $idPlugNotas, string $justificativa) :array
    {
        $result = $this->post("/nfe/$idPlugNotas/cancelamento", [
            'justificativa' => $justificativa
        ]);
        return $result;
    }

    /**
     * Função responsável por solicitar a justificativa de uma NFCe
     *
     * @param string $idPlugNotas ID da NFCe no PlugNotas
     * @param string $justificativa Texto de justificativa
     * @return array
     */
    public function cancelamentoNfce(string $idPlugNotas, string $justificativa) :array
    {
        $result = $this->post("/nfce/$idPlugNotas/cancelamento", [
            'justificativa' => $justificativa
        ]);
        return $result;
    }

    /**
     * Função responsável por solicitar o cancelamento de uma NFSe
     *
     * @param string $idPlugNotas ID da NFSe no PlugNotas
     * @param string $codigo Código do cancelamento
     * @param string $motivo Texto descritivo do motivo de cancelamento
     * @return array
     */
    public function cancelamentoNfse(string $idPlugNotas, $codigo = '', $motivo = '') :array
    {
        $result = $this->post("/nfse/cancelar/$idPlugNotas", [
            'codigo' => $codigo,
            'motivo' => $motivo
        ]);
        return $result;
    }

    /**
     * Função responsável por consultar o cancelamento de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @return array
     */
    public function consultaCancelamentoNfe(string $idPlugNotas) :array
    {
        $result = $this->get("/nfe/$idPlugNotas/cancelamento/status");
        return $result;
    }

    /**
     * Função responsável por consultar o cancelamento de uma NFCe
     *
     * @param string $idPlugNotas ID da NFCe no PlugNotas
     * @return array
     */
    public function consultaCancelamentoNfce(string $idPlugNotas) :array
    {
        $result = $this->get("/nfce/$idPlugNotas/cancelamento/status");
        return $result;
    }

    /**
     * Função responsável por consultar o cancelamento de uma NFSe
     *
     * @param string $protocoloPlugNotas Protocolo de cancelamento da NFSe
     * @return array
     */
    public function consultaCancelamentoNfse(string $protocoloPlugNotas) :array
    {
        $result = $this->get("/nfse/cancelar/status/$protocoloPlugNotas");
        return $result;
    }

    /**
     * Função responsável por buscar o XML do cancelamento de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @return array
     */
    public function xmlCancelamentoNfe(string $idPlugNotas) :array
    {
        $result = $this->get("/nfe/$idPlugNotas/cancelamento/xml");
        return $result;
    }

    /**
     * Função responsável por buscar o XML do cancelamento de uma NFCe
     *
     * @param string $idPlugNotas ID da NFCe no PlugNotas
     * @return array
     */
    public function xmlCancelamentoNfce(string $idPlugNotas) :array
    {
        $result = $this->get("/nfce/$idPlugNotas/cancelamento/xml");
        return $result;
    }

    /**
     * Função responsável por buscar o PDF do cancelamento de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @return array
     */
    public function pdfCancelamentoNfe(string $idPlugNotas) :array
    {
        $result = $this->get("/nfe/$idPlugNotas/cancelamento/pdf");
        return $result;
    }

    /**
     * Função responsável por baixar o Recibo Provisório de Serviço (RPS)
     *
     * @param string $idPlugNotas ID da NFSe no PlugNotas
     * @return array
     */
    public function downloadRpsNfse(string $idPlugNotas) :array
    {
        $result = $this->get("/nfse/rps/pdf/$idPlugNotas");
        return $result;
    }

    /**
     * Função responsável por resolver a sincronização de uma NFSe com a prefeitura
     *
     * @param string $idPlugNotas ID da NFSe no PlugNotas
     * @param string $identificacaoNota Identificação da nota na prefeitura
     * @return array
     */
    public function resolveNfse(string $idPlugNotas, string $identificacaoNota = '') :array
    {
        $result = $this->post("/nfse/resolve/$idPlugNotas", [
            'identificacaoNota' => $identificacaoNota,
        ]);
        return $result;
    }

    /**
     * Função responsável por enviar por e-mail XML e PDF da NFSe para os destinarários
     *
     * @param string $idPlugNotas ID da NFSe no PlugNotas
     * @return array
     */
    public function enviarEmailNfse(string $idPlugNotas, array $destinatarios) :array
    {
        $result = $this->post("/nfse/email/$idPlugNotas", [
            'destinatarios' => $destinatarios,
        ]);
        return $result;
    }

    /**
     * Função responsável por enviar o arquivo com AIDFs dos municípios do padrão Governa
     *
     * @param string $cnpjPrestador CNPJ do prestador
     * @param $filePath pasta temporária onde o arquivo está armazenado
     * @param $serieRps série de RPS que deseja atualizar
     * @param $numeroRps número de RPS que deseja atualizar
     * @return array
     */
    public function enviaAIDF(string $cnpjPrestador, $filePath, $fileName, $serieRps = '', $numeroRps = '') :array
    {
        $method = 'POST';
        $url = '/nfse/governa/numeracao';
        $data = [
            'cnpj' => $cnpjPrestador,
            'file' => new CURLFile($filePath, 'text/plain', $fileName),
            'serieRpsAtualizar' => $serieRps,
            'numeroRpsAtualizar' => $numeroRps
        ];

        $result = $this->upload($url, $data, [], $method);

        return $result;
    }

    /**
    * Função responsável por consultar o retorno do arquivo com AIDFs dos municípios do padrão Governa
    *
    * @param string $cnpjPrestador CNPJ do prestador
    * @param string $protoloco ID da NFSe no PlugNotas
    * @return array
    */
    public function consultaAIDF(string $cnpjPrestador, string $plugNotasProtocol) :array
    {
        $result = $this->get("/nfse/governa/numeracao/$cnpjPrestador/$plugNotasProtocol");
        return $result;
    }

    /**
     * Função responsável por consultar disponibilidade de homologação do município (para NFSe) e metadados
     *
     * @param  string $codigoIbge
     * @return array
     */
    public function consultaHomologacaoNfse(string $codigoIbge) :array
    {
        $result = $this->get("/nfse/cidades/$codigoIbge");
        return $result;
    }

    /**
     * Função responsável por realizar a manifestação de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @param string $operacao Operação a ser realizada
     * @param string $justificativa Justificativa da operação, usado para Operação Não Realizada
     * @return array
     */
    public function manifestacaoNfe(string $idPlugNotas, string $operacao, string $justificativa = '') :array
    {
        // Verifica se foi passado operações válidas de acordo com o PlugNotas
        if (!in_array($operacao, ['CONFIRMACAODAOPERACAO', 'DESCONHECIMENTODAOPERACAO', 'OPERACAONAOREALIZADA'])) {
            throw new \Exception("Operação inválida, usar CONFIRMACAODAOPERACAO, DESCONHECIMENTODAOPERACAO ou OPERACAONAOREALIZADA");
        }

        $dadosManifestacao = [
            'operacao' => $operacao
        ];

        if ($operacao == 'DESCONHECIMENTODAOPERACAO') {
            $dadosManifestacao['justificativa'] = $justificativa;
        }

        $result = $this->post("nfe/$idPlugNotas/manifestacao", $dadosManifestacao);
        return $result;
    }

    /**
     * Função responsável por consultar a manifestação de uma NFe
     *
     * @param string $idPlugNotas ID da NFe no PlugNotas
     * @return array
     */
    public function consultaManifestacaoNfe(string $idPlugNotas) :array
    {
        $result = $this->get("/nfe/$idPlugNotas/manifestacao/status");
        return $result;
    }

    /**
     * Função responsável pelo envio de dados de certificado para o Plugnotas
     *
     * @param array $dataCertificado Dados do certificado contendo o conteudo e senha
     * @return array
     */
    public function enviaCertificado(array $dataCertificado, $cnpj = null, $vencimento = null) :array
    {
        $url = '/certificado';
        if (!empty($cnpj)) {
            $url .= "/$cnpj";
        }
        $result = $this->get($url);

        $id = '';
        if ($result['httpCode'] == 200 && !empty($cnpj) && !empty($vencimento)) {
            $date = explode(' ', $result['body']->vencimento)[0];
            if ($date === $vencimento) {
                $id = $result['body']->id;
            }
        }

        $method = 'POST';
        $url = '/certificado';
        $data = [
            'arquivo' => new CURLFile($dataCertificado['path'], 'application/octet-stream', $dataCertificado['name']),
            'senha' => $dataCertificado['password']
        ];

        if (!empty($id)) {
            $url .= "/$id";
            $method = 'PUT';
        }

        $result = $this->upload($url, $data, [], $method);
        return $result;
    }

    /**
     * Função responsável por buscar os dados de uma empresa no PlugNotas
     *
     * @param array $cnpj CNPJ da empresa (Caso não informado retornará todas as empresas)
     * @return array
     */
    public function buscaEmpresa(string $cnpj = '') :array
    {
        $result = $this->get("empresa/$cnpj");
        return $result;
    }

    /**
     * Função responsável por cadastrar os dados de uma empresa no PlugNotas
     *
     * @param array $dataEmpresa Dados da empresa
     * @return array
     */
    public function cadastraEmpresa(array $dataEmpresa) :array
    {
        $result = $this->post("empresa", $dataEmpresa);
        return $result;
    }

    /**
     * Função responsável por atualizar os dados de uma empresa no PlugNotas
     *
     * @param string $cnpjEmpresa CNPJ da empresa a ser atualizada
     * @param array $dataEmpresa Dados da empresa
     * @return array
     */
    public function atualizaEmpresa(string $cnpjEmpresa, array $dataEmpresa) :array
    {
        $result = $this->patch("empresa/$cnpjEmpresa", $dataEmpresa);
        return $result;
    }

    /**
     * Função responsável por atualizar as séries NFe de uma empresa no PlugNotas
     *
     * @param  string $array $dadosEmpresa Array com dados da empresa a ser atualizada
     * @param  array  $serie Array de series contendo serie e numero
     * @return array
     */
    public function atualizaSeriesNfeEmpresa(array $dadosEmpresa, array $serie) :array
    {
        $dataEmpresa = [
            'nfe' => [
                'ativo' => true,
                'tipoContrato' => 0,
                'config' => [
                    'producao' => $dadosEmpresa['ambiente'] == 1,
                    'email' => [
                        'envio' => true
                    ],
                    'impressaoFcp' => true,
                    'impressaoPartilha' => true,
                    'dfe' => [
                        'ativo' => true
                    ],
                    'numeracao' => $serie
                ]
            ]
        ];

        $cnpj = $dadosEmpresa['cpfcnpj'];
        $result = $this->patch("empresa/$cnpj", $dataEmpresa);
        return $result;
    }

    /**
     * Função responsável por atualizar as séries NFCe de uma empresa no PlugNotas
     *
     * @param  string $array $dadosEmpresa Array com dados da empresa a ser atualizada
     * @param  array  $serie Array de series contendo serie e numero
     * @return array
     */
    public function atualizaSeriesNfceEmpresa(array $dadosEmpresa, array $serie) :array
    {
        $dataEmpresa = [
            'nfce' => [
                'ativo' => true,
                'tipoContrato' => 0,
                'config' => [
                    'producao' => $dadosEmpresa['ambiente'] == 1,
                    'email' => [
                        'envio' => true
                    ],
                    'sefaz' => [
                        'idCodigoSegurancaContribuinte' => $dadosEmpresa['id_token_csc'],
                        'codigoSegurancaContribuinte' => $dadosEmpresa['token_csc'],
                    ],
                    'numeracao' => $serie
                ]
            ]
        ];
        $cnpj = $dadosEmpresa['cpfcnpj'];
        $result = $this->patch("empresa/$cnpj", $dataEmpresa);
        return $result;
    }

    /**
     * Função responsável por atualizar as séries NFSe de uma empresa no PlugNotas
     *
     * @param  string $array $dadosEmpresa Array com dados da empresa a ser atualizada
     * @param  array  $serie Array de series contendo serie e numero
     * @return array
     */
    public function atualizaSeriesNfseEmpresa(array $dadosEmpresa, array $serie) :array
    {
        $dataEmpresa = [
            'nfse' => [
                'ativo' => true,
                'tipoContrato' => 0,
                'config' => [
                    'producao' => $dadosEmpresa['ambiente'] == 1,
                    'nfseNacional' => $dadosEmpresa['nfse_nacional'],
                    'calculoAutomaticoIbpt' => [
                        'ativo' => 'true'
                    ],
                    'rps' => [
                        'numeracao' => $serie
                    ]
                ]
            ]
        ];
        $cnpj = $dadosEmpresa['cpfcnpj'];
        $result = $this->patch("empresa/$cnpj", $dataEmpresa);
        return $result;
    }

    /**
     * Execute a GET Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function get(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [];

        if (!empty($headers) && is_array($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a POST Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function post(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body)
        ];

        if (!empty($headers) && is_array($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a PUT Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function put(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($body)
        ];

        if (!empty($headers) && is_array($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a PATCH Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function patch(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "PATCH",
            CURLOPT_POSTFIELDS => json_encode($body)
        ];

        if (!empty($headers) && is_array($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a DELETE Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function delete(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "DELETE"
        ];

        if (!empty($headers) && is_array($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a OPTION Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function options(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "OPTIONS"
        ];

        if (!empty($headers) && is_array($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a UPLOAD Request
     *
     * @param string $path
     * @param array $body
     * @param array $params
     * @param string $method Método da requisição
     * @return array
     */
    private function upload(string $path, array $body = [], array $params = [], string $method = 'POST') :array
    {
        $opts = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body
        ];
        $this->isUpload = true;

        $exec = $this->execute($path, $opts, $params);
        // Remove a flag de upload para não afetar requisições seguintes
        $this->isUpload = false;

        return $exec;
    }

    /**
     * Função responsável por realizar a requisição e devolver os dados
     *
     * @param string $path Rota a ser acessada
     * @param array $opts Opções do CURL
     * @param array $params Parametros query a serem passados para requisição
     *
     * @access private
     * @return array
     */
    private function execute(string $path, array $opts = [], array $params = []) :array
    {
        if (!preg_match("/^\//", $path)) {
            $path = '/' . $path;
        }

        if ($this->isProduction) {
            // Produção
            $url = 'https://api.plugnotas.com.br';
        } else {
            // Homologação
            $url = 'https://api.sandbox.plugnotas.com.br';
        }

        $url .= $path;

        $curlC = curl_init();

        $opts[CURLOPT_HTTPHEADER] = array_merge(isset($opts[CURLOPT_HTTPHEADER]) ? $opts[CURLOPT_HTTPHEADER] : [], [
            'x-api-key: '.$this->authToken
        ]);
        if ($this->isUpload) {
            $opts[CURLOPT_HTTPHEADER][] = 'multipart/form-data';
        } else {
            $opts[CURLOPT_HTTPHEADER][] = 'Content-type: application/json';
        }

        if (!empty($opts)) {
            curl_setopt_array($curlC, $opts);
        }

        if (!empty($params)) {
            $paramsJoined = [];

            foreach ($params as $param) {
                if (isset($param['name']) && !empty($param['name']) && isset($param['value']) && !empty($param['value'])) {
                    $paramsJoined[] = urlencode($param['name'])."=".urlencode($param['value']);
                }
            }

            if (!empty($paramsJoined)) {
                $params = '?'.implode('&', $paramsJoined);
                $url = $url.$params;
            }
        }

        curl_setopt($curlC, CURLOPT_URL, $url);
        curl_setopt($curlC, CURLOPT_RETURNTRANSFER, true);
        $retorno = curl_exec($curlC);
        $info = curl_getinfo($curlC);
        $error = curl_error($curlC);
        curl_close($curlC);

        $content_type = "";
        if ($info["content_type"] !== null) {
            $content_type_array = explode(";", $info['content_type']);
            $content_type = $content_type_array[0];
        }
        $return["body"] = $content_type === "application/json" ? json_decode($retorno) : $retorno;
        $return["httpCode"] = $info["http_code"];
        $return["info"] = $info;
        if (!empty($error)) {
            $return["error"] = $error;
        }

        return $return;
    }
}
