<?php
namespace ddr;

/**
 * Description of mail_simple
 * 
 * abstract class to send text mails with attachments
 * 
 *
 * @author ddr
 * @orginal_by anda
 * @link http://www.php.net/manual/de/function.mail.php#105661 orginal script
 */
abstract class mail_simple {

    private static $to;
    private static $to_name;
    private static $from;
    private static $from_name;
    private static $bcc;
    private static $bcc_name;
    private static $subject;
    private static $msg;
    private static $files;
    private static $mime_boundary;

    private static function get_addr($for) {
        $name = self::${$for . "_name"};
        return ucfirst($for) . ": " . self::$for . $name;
    }

    private static function set_addr($for, $addr, $name = "") {
        if ($name != "") {
            $name = "<" . $name . ">";
        } else {
            $name = "";
        }

        self::$for = $addr;
        self::${$for . "_name"} = $name;
    }

    private static function file_content($fname) {
        $fsize = filesize($fname);
        $fp = @fopen($fname, "rb");
        $data = fread($fp, $fsize);
        fclose($fp);
        return $data;
    }

    private static function prepare_files_array() {
        if ((!is_array(self::$files)) && (self::$files != "")) {
            self::$files = array(self::$files);
        }
    }

    private static function prepare_files() {

        $message = "";
        foreach (self::$files as $file) {
            if (is_file($file)) {
                $fsize = filesize($file);

                $message .= "--" . self::mime_boundary() . "\n";

                $data_b64 = chunk_split(base64_encode(self::file_content($file)));
                $message .= "Content-Type: application/octet-stream; name=\"" . basename($file) . "\"\n" .
                        "Content-Description: " . basename($file) . "\n" .
                        "Content-Disposition: attachment;\n" . " filename=\"" . basename($file) . "\"; size=" . $fsize . ";\n" .
                        "Content-Transfer-Encoding: base64\n\n" . $data_b64 . "\n\n";
            }
        }
        $message .= "--" . self::mime_boundary() . "--";

        return $message;
    }

    public static function from($from, $name = "") {
        self::set_addr("from", $from, $name);
    }

    public static function to($to, $name = "") {
        self::set_addr("to", $to, $name);
    }

    public static function bcc($to, $name = "") {
        self::set_addr("bcc", $to, $name);
    }

    public static function subject($subject) {
        self::$subject = $subject;
    }

    public static function message($message) {
        self::$msg = $message;
    }

    public static function files($files) {
        self::$files = $files;
    }

    private static function mime_boundary() {
        // boundary
        if (self::$mime_boundary == "") {
            $semi_rand = md5(time());
            self::$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
        }
        return self::$mime_boundary;
    }

    private static function send_params($to = false, $message = false, $files = false, $from = false) {
        if ($to) {
            self::to($to);
        }
        if ($message) {
            self::message($message);
        }
        if ($files) {
            self::files($files);
        }
        if ($from) {
            self::from($from);
        }
    }

    public static function send($to = false, $message = false, $files = false, $from = false) {

        self::send_params($to, $message, $files, $from);

        if (!$from = self::get_addr("from")) {
            throw \LogicException("no sender specified");
        }
        if (!$to = self::get_addr("to")) {
            throw \LogicException("no recipient specified");
        }
        $subject = (self::subject) ? $this::subject : "File transfer (" . \count(self::files) . " files)";

        $message = this::message . "\n" .
                "--" . self::mime_boundary() . "\r\n" .
                "Content-Type: text/plain; charset=\"iso-8859-1\"\n" .
                "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n" . self::prepare_files() .
                "--" . self::mime_boundary() . "--";

        $headers = $from . "\r\n" .
                "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"" . self::mime_boundary() . "\"";

        $returnpath = "-f" . self::$from;

        $ok = \mail($to, $subject, $message, $headers, $returnpath);

        if ($ok) {
            self::$mime_boundary = "";
            return \count(self::$files);
        } else {
            return false;
        }
    }

}
