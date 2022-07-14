<?php
include "autoload.php";
class Developer extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function developerData($action_type, $string_query)
    {
        $profile_picture_filename = $_FILES["profile_picture"]["name"];
        $tempname = $_FILES["profile_picture"]["tmp_name"];
        $image_path = "../resources/images/" . $profile_picture_filename;
        $update_data = array(
            "name" => $_POST['name'],
            "email" => $_POST['email'],
            "phone" => $_POST['phone'],
            "location" => $_POST['location'],
            "profile_picture" => $image_path,
            "price_per_hour" => $_POST['price_per_hour'],
            "technology" => $_POST['technology'],
            "description" => $_POST['description'],
            "years_of_experience" => $_POST['years_of_experience'],
            "native_language" => $_POST['native_language'],
            "linkedin_profile_link" => $_POST['linkedin_profile_link'],
        );
        if ($action_type == "update") {
            $update_data['id'] = $_GET['id'];
        }

        // Get all the submitted data from the form

        // Now let's move the uploaded image into the folder: image
        if (move_uploaded_file($tempname, $image_path)) {
            $msg = "Image uploaded successfully";
        } else {
            $msg = "Failed to upload image";
        }

        if ($action_type == "update") {
            if (empty($_FILES["profile_picture"]["name"])) {
                $string_query = str_replace("profile_picture=:profile_picture,", "", $string_query);
                unset($update_data['profile_picture']);
                return $this->query($string_query, $update_data);
            } else {
                return $this->query($string_query, $update_data);
            }
        }
        return $this->query($string_query, $update_data);

    }

    public function updateDeveloper()
    {
        $string_query = "UPDATE developers SET
                      name=:name,
                      email=:email,
                      phone=:phone,
                      location=:location,
                      profile_picture=:profile_picture,
                      price_per_hour=:price_per_hour,
                      technology=:technology,
                      description=:description,
                      years_of_experience=:years_of_experience,
                      native_language=:native_language,
                      linkedin_profile_link=:linkedin_profile_link WHERE id=:id";

        $this->developerData("update", $string_query);
    }

    public function deleteDeveloper($table)
    {
        return $this->query("DELETE FROM $table WHERE id = :id", ["id" => $_GET['id']]);
    }

    function createDeveloper()
    {
        $string_query = "INSERT INTO developers(
                      name,
                      email,
                      phone,
                      location,
                      profile_picture,
                      price_per_hour,
                      technology,
                      description,
                      years_of_experience,
                      native_language,
                      linkedin_profile_link)
            VALUES(
                        :name,
                        :email,
                        :phone,
                        :location,
                        :profile_picture,
                        :price_per_hour,
                        :technology,
                        :description,
                        :years_of_experience,
                        :native_language,
                        :linkedin_profile_link
                        )";
        if ($this->developerData("insert", $string_query) > 0) {
            return 'Succesfully created a new person !';
        }
    }

    public function readDeveloper($table, $identify_developer = null)
    {
        if ($identify_developer !== null) {
            return $this->query("SELECT * FROM $table WHERE id=:id", ["id" => $identify_developer]);
        } else {
            return $this->query("SELECT * FROM $table");
        }
    }

    public function selection_data_fetch($data_type, $row, $row_param)
    {
        foreach ($data_type as $data) {
            if ($data == $row[$row_param]) {
                ?>
                <option value="<?php echo $data ?>" selected><?php echo $data ?></option>
                <?php
            } else {
                ?>
                <option value="<?php echo $data ?>"><?php echo $data ?></option>
                <?php
            }
        }
    }


    public function submit_developer_for_hire()
    {
        $now = date("Y-m-d H:i:s");
        $select_developer_to_hire = $_POST['select_developer_to_hire'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        foreach ($select_developer_to_hire as $single_dev) {
            $array_dev[] = $single_dev;
            $developer_for_hire_from_db_select = $this->row("SELECT * FROM hire_developers WHERE names = :names AND (:start_date BETWEEN start_date AND end_date OR :end_date  BETWEEN start_date AND end_date)", ["names" => $single_dev, "start_date" => $start_date, "end_date" => $end_date]);
            $check_rows = $developer_for_hire_from_db_select;
            $store_res[] = $check_rows;

            if ($check_rows) {
                header('Location: ../public/hiring.php');
                die("Select valid date");
            }
            // Validation checks.
            if ($end_date < $start_date) {
                header('Location: ../public/hiring.php');
                die("Select valid date");
            }
            if ($start_date < $now || $end_date < $now) {
                header('Location: ../public/hiring.php');
                die("Select valid date");
            }

            // Insert a single developer in the db as a record. If multiple developers are selected, foreach all the selected before exiting.
            if (count($store_res) !== count($select_developer_to_hire)) {
                continue;
            }
            foreach ($array_dev as $one_dev) {
                $this->query("INSERT INTO hire_developers(names, start_date, end_date) VALUES(:names, :start_date, :end_date)", ["names" => $one_dev, "start_date" => $_POST['start_date'], "end_date" => $_POST['end_date']]);
            }
        }
//            if($this->query("SELECT * FROM hire_developers WHERE NOW() > end_date=:end_date", ["end_date" => $end_date])) {
//                //    DELETE FROM hire_developers WHERE end_date < NOW()
//                $this->deleteDeveloper("hire_developers");
//                header('Location: ../public/hiring.php');
//                die("Select valid date");
//            }
    }

    public function fetch_hireable_developers($dev_parm)
    {
        asort($dev_parm);
        foreach ($dev_parm as $row) {
            ?>
            <option value="<?= $row['name']; ?>"><?= $row['name']; ?></option>
            <?php
        }
    }

    public function list_hired_developers($row_rams)
    {
        foreach ($row_rams as $row):
            ?>
            <tbody>
            <tr>
                <td><?= $row['names'] ?></td>
                <td><?= $row['start_date'] ?></td>
                <td><?= $row['end_date'] ?></td>
                <?php
                ?>
                <td><a href="../src/delete_hired_developer.php?id=<?= $row['id']; ?>">Delete</a></td>
                <?php
                ?>
            </tr>
            </tbody>
        <?php
        endforeach;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
    }


}