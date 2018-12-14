<?php

namespace RentaBundle\Controller;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use RentaBundle\Entity\Formulario;
use RentaBundle\Form\FormularioType;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

/**
 * Class RentaController
 *
 * @package RentaBundle\Controller
 */
class RentaController extends Controller
{
	public function queryAction()
	{

		$EM = $this->getDoctrine()->getManager();
		$Procesos = $EM->getRepository("RentaBundle:Proceso")->findAll();
		return $this->render("query.html.twig", [$Procesos]);
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function indexAction(Request $request)
	{
		$Formulario = new Formulario();
		$FormularioForm = $this->createForm(FormularioType::class, $Formulario);
		$FormularioForm->handleRequest($request);

		if ($FormularioForm->isSubmitted()) {
			$file = $FormularioForm["fichero"]->getData();
			$ext = $file->guessExtension();
			$file_name = time() . "." . $ext;
			$file->move("ficheros", $file_name);

			$firma = $FormularioForm["firma"]->getData();
			$ext = $firma->guessExtension();
			$firma_name = time() . "." . $ext;
			$firma->move("firmas", $firma_name);

			$texto = $FormularioForm["texto"]->getData();

			$params["fichero"] = $file_name;
			$params["firma"] = $firma_name;
			$params["texto"] = $texto;

			if ($FormularioForm["organismo"]->getData() == 1) { //SUMMA 112
				$params["organismo_desc"] = "SUMMA 112";
				$params["organismo"] = "1";
				return $this->render("success.html.twig", $params);
			}
		}

		return $this->render("formulario.html.twig", array(
			"form" => $FormularioForm->createView()
		));
	}

	/**
	 * @param $organismo
	 * @param $fichero
	 * @param $firma
	 * @param $texto
	 * @return string|null
	 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
	 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
	 * @throws \setasign\Fpdi\PdfParser\PdfParserException
	 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
	 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
	 */
	public function extraerAction($organismo, $fichero, $firma, $texto)
	{
		if ($organismo == 1) {
			return $this->procesarSumma($fichero, $firma, $texto);
		} else {
			return null;
		}
	}

	/**
	 * @param $fichero
	 * @param $log
	 * @return array
	 * @throws \Exception
	 */
	public function extraerNIFSumma($fichero, $log)
	{
		$filename = "ficheros/" . $fichero;
		$log->info("Comienza Extracción de los NIF FICHERO: " . $filename);
		$parser = new Parser();
		$pdf = $parser->parseFile($filename);
		$log->info("Parseo correcto del fichero");
		$pages = $pdf->getPages();
		$log->info("Extracción de paginas correcta");

		$pagina = 1;
		$NIF = [];

		$log->info("Extracción de NIF");

		// Extraer el NIF de todas los certificados, en el 2017 tiene dos paginas solo tratamos las
		// impares

		foreach ($pages as $page) {
			$text = $page->getText();
			$cadena = "";
			$ct = 10;
			$ctletra = 0;
			for ($i = 11; $i < strlen($text); $i++) {
				$ctletra++;
				if ($ctletra < $ct) {
					$cadena .= $text[$i];
				}
			}
			if ($pagina == 1) {
				$NIF [] = $cadena;
				$pagina = 0;
			} else {
				$pagina = 1;
			}
		}

		return ($NIF);

	}

	/**
	 * @param $NIF
	 * @param $fichero
	 * @param $firma
	 * @param $texto
	 * @param $log
	 * @return string
	 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
	 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
	 * @throws \setasign\Fpdi\PdfParser\PdfParserException
	 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
	 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
	 */
	public function generaZIPSUMMA($NIF, $fichero, $firma, $texto, $log)
	{
		$filename = "ficheros/" . $fichero;
		$firmaname = "firmas/" . $firma;

		$pagina = 1;
		$zip = new \ZipArchive();
		$zip_name = "zip/" . time() . ".zip";
		$zip_sinpath = time() . ".zip";
		$zip->open($zip_name, ZipArchive::CREATE);
		for ($i = 0; $i < count($NIF); $i++) {
			$fichero = new \setasign\Fpdi\FPDI();
			$paginas = $fichero->setSourceFile($filename);
			if (!$paginas) {
				print_r("ERROR AL LEER EL FICHERO:" . $filename);
			}

			$tplIdx = $fichero->importPage($pagina, '/MediaBox');
			$fichero->AddPage();
			$fichero->useTemplate($tplIdx);

			$pagina++;
			$tplIdx1 = $fichero->importPage($pagina, '/MediaBox');
			$fichero->AddPage();
			$fichero->useTemplate($tplIdx1);

			if ($firma != "Imagen de Firma") {
				$fichero->Image($firmaname, 60, 275, 35, 16);
			}

			$nombre = 'tmp\00_' . $NIF[$i] . '_' . $texto . '.pdf';
			$sinDir = '\00_' . $NIF[$i] . '_' . $texto . '.pdf';

			$log->info("Generado fichero: " . $nombre);
			$fichero->output("F", $nombre);

			$zip->addFile($nombre, $sinDir);
			$pagina++;
		}

		$log->info("PROCESO FINALIZADO: ");
		$zip->close();
		return $zip_sinpath;
	}

	/**
	 * @param $fichero
	 * @param $firma
	 * @param $texto
	 * @return string
	 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
	 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
	 * @throws \setasign\Fpdi\PdfParser\PdfParserException
	 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
	 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
	 */
	public function procesarSumma($fichero, $firma, $texto)
	{

		$FicheroLog = 'logs/procesarSUMMA.log';
		$repo = new RotatingFileHandler($FicheroLog, 30, Logger::INFO);
		$log = new Logger('extraer');
		$log->pushHandler($repo);
		$log->info("Fichero: " . $fichero);
		$log->info("Firma: " . $firma);
		$log->info("texto: " . $texto);

		//$NIF = $this->extraerNIFSUMMA($fichero,$log);
		$NIF[] = '50742048T';
		$log->info("Total Registros a Tratar: " . count($NIF));

		$zip_name = $this->generaZIPSUMMA($NIF, $fichero, $firma, $texto, $log);

		//echo $this->render("descarga.html.twig", array("fichero" => $zip_name));

		return $zip_name;

	}

	public function descargarAction($fichero)
	{

		$filename = 'zip/' . $fichero;
		$response = new Response();
		$response->headers->set('Content-type', 'application/octet-stream');
		$response->headers->set('Content-disposition', "attachment; filename=" . $filename);
		$response->headers->set('Content-Length', filesize($filename));
		$response->headers->set('Content-Transfer-Encoding', 'binary');
		$response->setContent(file_get_contents($filename));

		return $response;
	}
}
