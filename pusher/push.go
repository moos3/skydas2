package main

import (
	"bytes"
	"fmt"
	"log"
	"net/http"

	"github.com/divan/gorilla-xmlrpc/xml"
	"github.com/streadway/amqp"
)

func XmlRpcCall(method string, args struct{ who string }) (reply struct{ Message string }, err error) {
	buf, _ := xml.EncodeClientRequest(method, &args)

	resp, err := http.Post("http://voip.symplicity.com:8080/RPC", "text/xml", bytes.NewBuffer(buf))
	if err != nil {
		return
	}
	defer resp.Body.Close()

	err = xml.DecodeClientResponse(resp.Body, &reply)
	return

}

type FreeSWITCHFifo struct {
	Data struct {
		Queues []struct {
			Agents []struct {
				CallsAnswered string `json:"calls_answered"`
				ID            string `json:"id"`
				Name          string `json:"name"`
				NoAnswerCount string `json:"no_answer_count"`
				QueueJoinTime string `json:"queue_join_time"`
				Status        string `json:"status"`
			} `json:"agents"`
		} `json:queues`
		Callers []struct {
			AgentExten   string `json:"agent_exten"`
			AgentName    string `json:"agent_name"`
			AnsweredTime string `json:"answered_time"`
			CallerID     string `json:"caller_id"`
			CallerName   string `json:"caller_name"`
			HoldTime     string `json:"hold_time"`
			ID           string `json:"id"`
			Status       string `json:"status"`
		} `json:"callers"`
	} `json:"data"`
	Type string `json:"type"`
}

func failOnError(err error, msg string) {
	if err != nil {
		log.Fatalf("%s: %s", msg, err)
		panic(fmt.Sprintf("%s: %s", msg, err))
	}
}

func Panic(err error) {
	if err != nil {
		panic(err)
	}
}

func main() {
	conn, err := amqp.Dial("amqp://localhost:5672")
	Panic(err)
	defer conn.Close()

	ch, err := conn.Channel()
	Panic(err)
	defer ch.Close()

	err = ch.ExchangeDeclare(
		"updates",
		"fanout",
		true,
		false,
		false,
		false,
		nil,
	)
	Panic(err)

	err = ch.Publish(
		"updates",
		"",
		false,
		false,
		amqp.Publishing{
			ContentType: "text/plain",
			Body:        []byte("This is a test"),
		})

	Panic(err)
	log.Printf("Sent")
}
